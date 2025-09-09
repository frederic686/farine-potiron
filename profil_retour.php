<?php
/**
 * Contrôleur : controleur_retour_profil.php
 * Rôle : point de passage unique pour retourner vers la page profil
 * Redirige vers controleur_profil.php en propageant éventuellement ?updated=1
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=not_connected");
    exit;
}

$query = isset($_GET['updated']) ? "?updated=1" : "";
header("Location: profil.php{$query}");
exit;
