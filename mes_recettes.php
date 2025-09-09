<?php
/**

 */
session_start();
require_once "library/init.php";

if (empty($_SESSION['user_id'])) {
    header("Location: login.php?error=not_connected");
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$success  = $_GET['success'] ?? '';
$recettes = Recette::findByUser($userId);

require "templates/MesRecette.php";
