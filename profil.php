<?php
/**
 * Contrôleur : profil.php
 * ------------------------------------------------------------
 * 🎯 Rôle :
 *   - Vérifie si l’utilisateur est connecté
 *   - Charge les infos de l’utilisateur
 *   - Passe les infos à la vue monprofil.php
 */

session_start();

// Vérifier si l’utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=not_connected");
    exit;
}

// Charger la connexion BDD et modèle
require_once "library/init.php";

// Récupérer les infos de l’utilisateur connecté
$user = User::findById($_SESSION['user_id']); // méthode statique dans User.php

// Charger la vue
require "templates/MonProfil.php";
