<?php
/**
 * Contrôleur : controleur_infoprofil.php
 * Rôle : afficher le formulaire de MAJ profil et traiter la soumission
 * Entrées POST : email, password (optionnel), confirm_password
 * Sortie : $message et $user vers templates/infoprofil.php
 * Redirection succès : controleur_retour_profil.php?updated=1
 */
session_start();
require_once "library/init.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=not_connected");
    exit;
}

$message = "";
$user = User::findById($_SESSION['user_id']); // méthode statique dans User.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm  = $_POST['confirm_password'] ?? "";

    if ($email === "") {
        $message = "⚠️ L'email ne peut pas être vide.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ L'adresse email n'est pas valide.";
    } elseif ($password !== "" && $password !== $confirm) {
        $message = "⚠️ Les mots de passe ne correspondent pas.";
    } else {
        // Appliquer les changements
        $user->email = $email;
        if ($password !== "") {
            $user->password = password_hash($password, PASSWORD_BCRYPT);
        }
        if ($user->update()) {
            header("Location: profil_retour.php?updated=1");
            exit;
        } else {
            $message = "❌ Erreur lors de la mise à jour.";
        }
    }
}

require "templates/InfoProfil.php";
