<?php
session_start();
require_once "library/init.php";
// require_once "models/Recette.php";
// require_once "models/Ingredient.php";

function json_response(array $payload, int $code=200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

function is_ajax(): bool {
  return (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
  );
}

/** Récupère le catalogue des farines F&P via cURL (backend) */
function fetchCatalogueFarines(): array {
  $url = "https://api.mywebecom.ovh/play/fep/catalogue.php";
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 6,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER => ['Accept: application/json']
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($resp === false || $code < 200 || $code >= 300) { return []; }
  $data = json_decode($resp, true);
  return is_array($data) ? $data : [];
}

// Auth
if (empty($_SESSION['user_id'])) {
  if (is_ajax()) json_response(['ok'=>false,'msg'=>'Non connecté'], 401);
  header("Location: login.php?error=not_connected");
  exit;
}

$userId           = (int)$_SESSION['user_id'];
$catalogueFarines = fetchCatalogueFarines();

/* =========================
   ROUTES AJAX — SEULE logique d’écriture
   ========================= */
if (is_ajax() && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  try {
    switch ($action) {
      case 'delete': {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) json_response(['ok'=>false,'msg'=>'ID manquant'], 400);

        if (Recette::deleteOwned($id, $userId)) {
          json_response(['ok'=>true,'msg'=>'Recette supprimée','id'=>$id]);
        }
        json_response(['ok'=>false,'msg'=>'Suppression impossible'], 403);
      }

      case 'save': {
        $id          = (int)($_POST['id'] ?? 0);
        $titre       = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duree       = (int)($_POST['duree'] ?? 0);
        $difficulte  = $_POST['difficulte'] ?? 'facile';
        $ingFarines  = isset($_POST['ing_farine']) ? (array)$_POST['ing_farine'] : [];
        $ingNoms     = isset($_POST['ing_nom'])    ? (array)$_POST['ing_nom']    : [];
        $ingQtes     = isset($_POST['ing_qte'])    ? (array)$_POST['ing_qte']    : [];

        $diffOk = in_array($difficulte, ['très facile','facile','difficile'], true);
        if ($titre === '' || $description === '' || $duree <= 0 || !$diffOk) {
          json_response(['ok'=>false,'msg'=>'Champs invalides ou manquants'], 422);
        }

        // Reconstruire ingrédients + règle métier "au moins une farine F&P"
        $ings = [];
        $hasFarine = false;
        $max = max(count($ingFarines), count($ingNoms), count($ingQtes));
        for ($i=0; $i<$max; $i++) {
          $ref = trim($ingFarines[$i] ?? '');
          $nom = trim($ingNoms[$i] ?? '');
          $qte = trim($ingQtes[$i] ?? '');
          if ($ref !== '') {
            $lib = $catalogueFarines[$ref] ?? $ref;
            $nom = $lib;
            $hasFarine = true;
          }
          if ($nom === '' && $qte === '') continue;
          $ings[] = [
            'nom'        => $nom,
            'quantite'   => $qte,
            'ref_farine' => ($ref !== '') ? $ref : null,
          ];
        }
        if (!$hasFarine) {
          json_response(['ok'=>false,'msg'=>'Merci de sélectionner au moins une farine F&P'], 422);
        }

        $bdd->beginTransaction();

        if ($id === 0) {
          // Création
          $obj = new Recette();
          $obj->titre          = $titre;
          $obj->description    = $description;
          $obj->duree          = $duree;
          $obj->difficulte     = $difficulte;
          $obj->id_utilisateur = $userId;
          $newId = $obj->insert();

          Ingredient::insertMany($newId, $ings);
          $bdd->commit();

          json_response(['ok'=>true,'msg'=>'Créée','id'=>$newId]);
        } else {
          // Mise à jour
          $obj = new Recette();
          $obj->id          = $id;
          $obj->titre       = $titre;
          $obj->description = $description;
          $obj->duree       = $duree;
          $obj->difficulte  = $difficulte;

          if (!$obj->updateOwned($userId)) {
            throw new Exception('Update refusé (identifiant/propriété)');
          }
          Ingredient::deleteByRecette($id);
          Ingredient::insertMany($id, $ings);

          $bdd->commit();
          json_response(['ok'=>true,'msg'=>'Mise à jour','id'=>$id]);
        }
      }

      default:
        json_response(['ok'=>false,'msg'=>'Action inconnue'], 400);
    }
  } catch (Throwable $e) {
    if ($bdd->inTransaction()) $bdd->rollBack();
    json_response(['ok'=>false,'msg'=>'Erreur serveur: '.$e->getMessage()], 500);
  }
}

/* =========================
   RENDU PAGE (GET) — lecture seule
   ========================= */
$success  = $_GET['success'] ?? "";
$message  = "";

// Liste des recettes (colonne gauche)
$recettes = Recette::findByUser($userId);
if (!empty($recettes)) {
  foreach ($recettes as &$rec) {
    $rec['ingredients'] = Ingredient::getByRecette((int)$rec['id']);
  }
  unset($rec);
}

// Chargement de la recette en édition (colonne droite)
$rid         = (int)($_GET['id'] ?? 0);
$recette     = ['id'=>0,'titre'=>'','description'=>'','duree'=>'','difficulte'=>'facile'];
$ingredients = [];
if ($rid > 0) {
  $r = Recette::findByIdAndUser($rid, $userId);
  if ($r) {
    $recette     = $r;
    $ingredients = Ingredient::getByRecette($rid);
  } else {
    $message = "⚠️ Recette introuvable ou non autorisée.";
  }
}

// Pré-remplissage si vide (3 lignes)
if (empty($ingredients)) { $ingredients = [[],[],[]]; }

// Rendu
require "templates/MesRecette.php";
