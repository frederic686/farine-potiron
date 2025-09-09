<?php
require_once "library/init.php";
session_start();

/* --- CSRF bootstrap --- */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$CSRF = $_SESSION['csrf_token'];

$userId          = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isConnected     = $userId > 0;
$redirectToLogin = "login.php?error=need_login&redirect=" . urlencode("recette.php");

/* =========================
   ========== GET ==========
   Recherche par mot-clé ?q=
   (titre, description, ingrédients, farines F&P)
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
  $q     = trim((string)($_GET['q'] ?? ''));
  $info  = $_GET['info']  ?? '';
  $error = $_GET['error'] ?? '';

  try {
    // 1) Résultats de recherche
    $recettes = Recette::searchByKeywordWithIngredients($q);

    // 2) Préparations (identique à ton flux habituel)
    $ids = array_map(fn($r) => (int)$r['id'], $recettes);

    // Auteurs
    $ownerIds = [];
    foreach ($recettes as $r0) {
      $rid     = (int)$r0['id'];
      $ownerId = isset($r0['id_utilisateur'])
        ? (int)$r0['id_utilisateur']
        : (int)(Recette::ownerId($rid) ?? 0);
      if ($ownerId > 0) $ownerIds[] = $ownerId;
    }
    $ownerPseudos = !empty($ownerIds) ? User::pseudosByIds($ownerIds) : [];

    // Maps
    $ingredientsByRecette = !empty($ids) ? Ingredient::mapByRecetteIds($ids)          : [];
    $commentsByRecette    = !empty($ids) ? Commentaire::mapByRecetteIdsWithUsers($ids) : [];
    $notesAgg             = !empty($ids) ? Note::averagesByRecetteIds($ids)            : [];
    $userNotesMap         = !empty($ids) ? Note::mapByRecetteAndUser($ids)             : [];

    // Pré-remplissage édition (si connecté)
    $ownLatestCommentByRecette = ($isConnected && !empty($ids))
      ? Commentaire::latestByUserForRecetteIds($ids, $userId)
      : [];

    // Assemblage pour la vue
    foreach ($recettes as &$r) {
      $rid     = (int)$r['id'];
      $ownerId = isset($r['id_utilisateur']) ? (int)$r['id_utilisateur'] : (int)(Recette::ownerId($rid) ?? 0);

      $r['ingredients'] = $ingredientsByRecette[$rid] ?? [];

      $r['commentaires'] = [];
      if (!empty($commentsByRecette[$rid])) {
        foreach ($commentsByRecette[$rid] as $c) {
          $uid       = (int)$c['id_utilisateur'];
          $c['note'] = $userNotesMap[$rid][$uid] ?? null;
          $r['commentaires'][] = $c;
        }
      }

      $r['moyenne_note'] = $notesAgg[$rid]['moyenne'] ?? null;
      $r['nb_notes']     = (int)($notesAgg[$rid]['nb_notes'] ?? 0);

      $r['auteur_pseudo'] = $ownerId > 0 ? ($ownerPseudos[$ownerId] ?? 'Anonyme') : 'Anonyme';
      $r['can_comment']   = $isConnected && ($ownerId !== $userId);

      $r['own_note']          = $isConnected ? ($userNotesMap[$rid][$userId] ?? 0) : 0;
      $ownCom                 = $isConnected ? ($ownLatestCommentByRecette[$rid] ?? null) : null;
      $r['own_comment_id']    = $ownCom ? (int)$ownCom['id'] : 0;
      $r['own_comment_texte'] = $ownCom ? (string)$ownCom['texte'] : '';
      $r['own_comment_date']  = $ownCom ? ((string)($ownCom['date_update'] ?: $ownCom['date_creation'])) : '';
      $r['is_edit']           = ($r['own_note'] > 0) || ($r['own_comment_id'] > 0);
    }
    unset($r);

    // Filtre pour réafficher la barre
    $filters = ['q' => $q];
    $catalogueFarines = []; // non requis pour cette vue

    require __DIR__ . "/templates/recette_public.php";
    exit;

  } catch (Throwable $e) {
    header("Location: recette.php?error=" . urlencode("Erreur recherche : " . $e->getMessage()));
    exit;
  }
}

/* ============================================
   ========== POST commenter / editer =========
   ============================================ */

$redirect = $_POST['redirect'] ?? 'recette.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$redirect}");
    exit;
}

$action = $_POST['action'] ?? '';

/** Vérifs communes */
function guard_csrf_and_auth(string $redirect): int {
    $csrf_session = $_SESSION['csrf_token'] ?? '';
    $csrf_post    = $_POST['csrf'] ?? '';
    if (!$csrf_session || !$csrf_post || !hash_equals($csrf_session, $csrf_post)) {
        header("Location: {$redirect}?error=" . urlencode("Jeton CSRF invalide."));
        exit;
    }
    $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($uid <= 0) {
        header("Location: login.php?error=need_login&redirect=" . urlencode($redirect));
        exit;
    }
    return $uid;
}

if ($action === 'commenter') {
    $userId   = guard_csrf_and_auth($redirect);
    $idRecette= (int)($_POST['id_recette'] ?? 0);
    $note     = (int)($_POST['note'] ?? 0);
    $texte    = trim((string)($_POST['texte'] ?? ''));

    if ($note < 0) $note = 0;
    if ($note > 5) $note = 5;

    try {
        if ($idRecette <= 0 || !Recette::exists($idRecette)) {
            throw new RuntimeException("Recette invalide.");
        }

        $ownerId = (int)(Recette::ownerId($idRecette) ?? 0);
        if ($ownerId === $userId) {
            throw new RuntimeException("Vous ne pouvez pas commenter / noter votre propre recette.");
        }

        $touched = false;

        if ($note >= 1 && $note <= 5) { Note::upsert($idRecette, $userId, $note); $touched = true; }
        if ($texte !== '') {
            Commentaire::create([
                'id_recette'     => $idRecette,
                'id_utilisateur' => $userId,
                'texte'          => $texte,
            ]);
            $touched = true;
        }

        if (!$touched) {
            throw new RuntimeException("Aucun contenu à publier.");
        }

        header("Location: {$redirect}?info=" . urlencode("Merci pour votre contribution !"));
        exit;
    } catch (Throwable $e) {
        header("Location: {$redirect}?error=" . urlencode("Erreur : " . $e->getMessage()));
        exit;
    }
}

if ($action === 'editer') {
    $userId    = guard_csrf_and_auth($redirect);
    $idRecette = (int)($_POST['id_recette'] ?? 0);
    $note      = (int)($_POST['note'] ?? 0);
    $texte     = trim((string)($_POST['texte'] ?? ''));
    $commentId = (int)($_POST['comment_id'] ?? 0);

    if ($note < 0) $note = 0;
    if ($note > 5) $note = 5;

    try {
        if ($idRecette <= 0 || !Recette::exists($idRecette)) {
            throw new RuntimeException("Recette invalide.");
        }

        $ownerId = (int)(Recette::ownerId($idRecette) ?? 0);
        if ($ownerId === $userId) {
            throw new RuntimeException("Vous ne pouvez pas modifier une réaction sur votre propre recette.");
        }

        $touched = false;

        // Le commentaire édité doit appartenir à l'utilisateur
        if ($commentId > 0) {
            if (!Commentaire::existsForUser($commentId, $userId)) {
                throw new RuntimeException("Ce commentaire ne vous appartient pas.");
            }
        }

        // 1) Note (upsert)
        if ($note >= 1 && $note <= 5) {
            Note::upsert($idRecette, $userId, $note);
            $touched = true;
        }

        // 2) Commentaire
        if ($commentId > 0 && $texte !== '') {
            Commentaire::updateByIdAndUser($commentId, $userId, $texte);
            $touched = true;
        } elseif ($commentId === 0 && $texte !== '') {
            Commentaire::create([
                'id_recette'     => $idRecette,
                'id_utilisateur' => $userId,
                'texte'          => $texte,
            ]);
            $touched = true;
        }

        if (!$touched) {
            header("Location: {$redirect}?info=" . urlencode("Aucune modification apportée."));
            exit;
        }

        header("Location: {$redirect}?info=" . urlencode("Modifications enregistrées."));
        exit;
    } catch (Throwable $e) {
        header("Location: {$redirect}?error=" . urlencode("Erreur : " . $e->getMessage()));
        exit;
    }
}

header("Location: {$redirect}?error=" . urlencode("Action invalide."));
exit;
