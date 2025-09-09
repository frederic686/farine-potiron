<?php
session_start();
require_once "library/init.php";
// require_once "models/Recette.php";
// require_once "models/Ingredient.php";
// ^^^ décommente si tu n'as pas d'autoload

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

if (empty($_SESSION['user_id'])) {
  header("Location: login.php?error=not_connected");
  exit;
}

$userId  = (int)$_SESSION['user_id'];
$message = "";
$success = $_GET['success'] ?? "";

/** Catalogue farines (ref => libellé) */
$catalogueFarines = fetchCatalogueFarines();

/* =========================
   LISTE (colonne gauche)
   ========================= */
$recettes = Recette::findByUser($userId);

/* Pour l’accordéon : on enrichit chaque recette avec ses ingrédients */
if (!empty($recettes)) {
  foreach ($recettes as &$rec) {
    $rec['ingredients'] = Ingredient::getByRecette((int)$rec['id']); // attend: nom, quantite, (optionnel) ref_farine
  }
  unset($rec);
}

/* =========================
   EDITION (colonne droite)
   ========================= */
$rid         = (int)($_GET['id'] ?? 0);
$recette     = ['id'=>0, 'titre'=>'', 'description'=>'', 'duree'=>'', 'difficulte'=>'facile'];
$ingredients = [];

/* Si édition, charger la recette et ses ingrédients */
if ($rid > 0) {
  $r = Recette::findByIdAndUser($rid, $userId);
  if ($r) {
    $recette     = $r;
    $ingredients = Ingredient::getByRecette($rid);
  } else {
    $message = "⚠️ Recette introuvable ou non autorisée.";
  }
}

/* =========================
   TRAITEMENT POST (create / update / delete)
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postId      = (int)($_POST['id'] ?? 0);
  $titre       = trim($_POST['titre'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $duree       = (int)($_POST['duree'] ?? 0);
  $difficulte  = $_POST['difficulte'] ?? 'facile';

  // Ingrédients saisis
  $ingFarines  = $_POST['ing_farine'] ?? [];   // références F&P (ex: FEP-XXXX)
  $ingNoms     = $_POST['ing_nom'] ?? [];      // nom libre
  $ingQtes     = $_POST['ing_qte'] ?? [];      // quantités
  $supprimer   = isset($_POST['supprimer']);

  $diffOk = in_array($difficulte, ['très facile','facile','difficile'], true);

  if ($supprimer && $postId > 0) {
    if (Recette::deleteOwned($postId, $userId)) {
      header("Location: MajRecette.php?success=deleted");
      exit;
    } else {
      $message = "❌ Suppression impossible.";
    }
  } else {
    if ($titre === '' || $description === '' || $duree <= 0 || !$diffOk) {
      $message = "⚠️ Champs invalides ou manquants.";
    } else {
      // Reconstruire les ingrédients (et conserver ref_farine si sélectionnée)
      $ings = [];
      $hasFarine = false;
      $max = max(count($ingFarines), count($ingNoms), count($ingQtes));

      for ($i=0; $i<$max; $i++) {
        $ref = trim($ingFarines[$i] ?? '');
        $nom = trim($ingNoms[$i] ?? '');
        $qte = trim($ingQtes[$i] ?? '');

        // si une ref F&P est choisie, on force le nom au libellé du catalogue
        if ($ref !== '') {
          $lib = $catalogueFarines[$ref] ?? $ref;
          $nom = $lib;
          $hasFarine = true;
        }

        // ignorer les lignes vides
        if ($nom === '' && $qte === '') continue;

        $ings[] = [
          'nom'        => $nom,
          'quantite'   => $qte,
          'ref_farine' => ($ref !== '') ? $ref : null,
        ];
      }

      // Règle métier : au moins une farine F&P
      if (!$hasFarine) {
        $message = "⚠️ Merci de sélectionner au moins une farine F&P dans la liste.";
      } else {
        try {
          $bdd->beginTransaction();

          if ($postId === 0) {
            // Création
            $obj = new Recette();
            $obj->titre          = $titre;
            $obj->description    = $description;
            $obj->duree          = $duree;
            $obj->difficulte     = $difficulte;
            $obj->id_utilisateur = $userId;
            $newId = $obj->insert();

            Ingredient::insertMany($newId, $ings); // doit gérer nom, quantite, ref_farine
            $bdd->commit();
            header("Location: MajRecette.php?success=created");
            exit;

          } else {
            // Mise à jour
            $obj = new Recette();
            $obj->id            = $postId;
            $obj->titre         = $titre;
            $obj->description   = $description;
            $obj->duree         = $duree;
            $obj->difficulte    = $difficulte;

            if (!$obj->updateOwned($userId)) {
              throw new Exception("Update refusé (identifiant/propriété).");
            }

            Ingredient::deleteByRecette($postId);
            Ingredient::insertMany($postId, $ings);

            $bdd->commit();
            header("Location: MajRecette.php?success=updated");
            exit;
          }
        } catch (Throwable $e) {
          if ($bdd->inTransaction()) { $bdd->rollBack(); }
          $message = "❌ Erreur : ".$e->getMessage();
        }
      }
    }
  }

  // En cas d'erreur : recharger le formulaire avec la saisie
  $recette = [
    'id'          => $postId,
    'titre'       => $titre,
    'description' => $description,
    'duree'       => $duree,
    'difficulte'  => $difficulte
  ];
  $ingredients = $ings ?? $ingredients;
}

/* Pré-remplissage minimal des lignes d’ingrédients côté formulaire */
if (empty($ingredients)) { $ingredients = [[],[],[]]; }

/* =========================
   Rendu vue
   ========================= */
require "templates/MesRecette.php";
