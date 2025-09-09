<?php
session_start();
require_once "library/init.php";

/* CSRF */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$CSRF = $_SESSION['csrf_token'];

$info  = $_GET['info']  ?? "";
$error = $_GET['error'] ?? "";

$userId      = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isConnected = $userId > 0;

/* Données */
$recettes = Recette::allPublic();
$ids      = array_map(fn($r) => (int)$r['id'], $recettes);

/* Auteurs (pseudo) */
$ownerIds = [];
foreach ($recettes as $r0) {
  $rid = (int)$r0['id'];
  $ownerId = isset($r0['id_utilisateur'])
    ? (int)$r0['id_utilisateur']
    : (int)(Recette::ownerId($rid) ?? 0);
  if ($ownerId > 0) $ownerIds[] = $ownerId;
}
$ownerPseudos = !empty($ownerIds) ? User::pseudosByIds($ownerIds) : [];

/* Maps */
$ingredientsByRecette = !empty($ids) ? Ingredient::mapByRecetteIds($ids)          : [];
$commentsByRecette    = !empty($ids) ? Commentaire::mapByRecetteIdsWithUsers($ids) : [];
$notesAgg             = !empty($ids) ? Note::averagesByRecetteIds($ids)            : [];
$userNotesMap         = !empty($ids) ? Note::mapByRecetteAndUser($ids)             : [];

/* Pré-remplissage édition (si connecté) */
$ownLatestCommentByRecette = ($isConnected && !empty($ids))
  ? Commentaire::latestByUserForRecetteIds($ids, $userId)
  : [];

/* Préparation pour la vue */
foreach ($recettes as &$r) {
  $rid = (int)$r['id'];
  $ownerId = isset($r['id_utilisateur']) ? (int)$r['id_utilisateur'] : (int)(Recette::ownerId($rid) ?? 0);

  // Ingrédients
  $r['ingredients'] = $ingredientsByRecette[$rid] ?? [];

  // Commentaires (avec note de leur auteur)
  $r['commentaires'] = [];
  if (!empty($commentsByRecette[$rid])) {
    foreach ($commentsByRecette[$rid] as $c) {
      $uid = (int)$c['id_utilisateur'];
      $c['note'] = $userNotesMap[$rid][$uid] ?? null;
      $r['commentaires'][] = $c;
    }
  }

  // Agrégats
  $r['moyenne_note'] = $notesAgg[$rid]['moyenne'] ?? null;
  $r['nb_notes']     = (int)($notesAgg[$rid]['nb_notes'] ?? 0);

  // Auteur (pseudo)
  $r['auteur_pseudo'] = $ownerId > 0 ? ($ownerPseudos[$ownerId] ?? 'Anonyme') : 'Anonyme';

  // Interdiction self-comment
  $r['can_comment']  = $isConnected && ($ownerId !== $userId);

  // Pré-remplissage "edit" si l'utilisateur a déjà réagi
  $r['own_note']          = $isConnected ? ($userNotesMap[$rid][$userId] ?? 0) : 0;
  $ownCom                 = $isConnected ? ($ownLatestCommentByRecette[$rid] ?? null) : null;
  $r['own_comment_id']    = $ownCom ? (int)$ownCom['id'] : 0;
  $r['own_comment_texte'] = $ownCom ? (string)$ownCom['texte'] : '';
  $r['own_comment_date']  = $ownCom ? ((string)($ownCom['date_update'] ?: $ownCom['date_creation'])) : '';
  $r['is_edit']           = ($r['own_note'] > 0) || ($r['own_comment_id'] > 0);
}
unset($r);

$redirectToLogin = "login.php?error=need_login&redirect=" . urlencode("recette.php");

/* Rendu */
require "templates/recette_public.php";
