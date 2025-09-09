<?php
/**
 * Contrôleur : controleur_login.php
 * ------------------------------------------------------------
 * 🎯 Rôle :
 *   - Afficher le formulaire de connexion
 *   - Vérifier les identifiants utilisateur
 *   - Si connexion réussie → rediriger vers profil.php
 *
 * 📥 Entrées :
 *   - identifiant : pseudo ou email
 *   - password    : mot de passe
 *
 * 📤 Sorties :
 *   - $message : message d’erreur affiché dans la vue
 */

require_once "library/init.php";


session_start();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? "");
    $password    = $_POST['password'] ?? "";

    if (empty($identifiant) || empty($password)) {
        $message = "⚠️ Veuillez remplir tous les champs.";
    } else {
        // Vérifier utilisateur
        $user = User::findByIdentifiant($identifiant); // méthode à coder dans User.php

        if ($user && password_verify($password, $user->password)) {
            // ✅ Connexion réussie → stocker en session
            $_SESSION['user_id'] = $user->id;
            $_SESSION['pseudo']  = $user->pseudo;

            // Redirection vers profil
            header("Location: profil.php");
            exit;
        } else {
            $message = "❌ Identifiant ou mot de passe incorrect.";
        }
    }
}

// Charger la vue
require "templates/formulaire_login.php";
