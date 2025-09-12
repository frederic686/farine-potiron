<?php
/**
 * Contrôleur : controleur_inscription.php
 * - Affiche le formulaire d'inscription
 * - Valide et crée l'utilisateur (avec honeypot)
 */

require_once "library/init.php";

$message   = "";
$pseudo    = "";
$email     = "";
$verif_mot = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Champs visibles
    $pseudo     = trim($_POST['pseudo'] ?? "");
    $email      = trim($_POST['email'] ?? "");
    $password   = $_POST['password'] ?? "";
    $confirm    = $_POST['confirm_password'] ?? "";
    $notabot    = isset($_POST['notabot']);
    $verif_mot  = strtolower(trim($_POST['verif_mot'] ?? ""));

    // Pièges honeypot
    $hp_website = trim($_POST['website'] ?? "");            // doit rester vide
    $started_at = (int)($_POST['form_started'] ?? 0);       // timestamp submit-1
    $now        = time();
    $elapsed    = $now - $started_at;                       // durée de remplissage

    // 1) Détection honeypot: champ caché rempli => bot
    if ($hp_website !== "") {
        http_response_code(400);
        $message = "❌ Requête invalide.";
    }
    // 2) Détection honeypot: envoi trop rapide (ex: < 3 secondes)
    elseif ($started_at <= 0 || $elapsed < 3) {
        http_response_code(400);
        $message = "❌ Requête invalide.";
    }
    // 3) Validations usuelles
    elseif (empty($pseudo) || empty($email) || empty($password) || empty($confirm)) {
        $message = "⚠️ Tous les champs doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ Adresse email invalide.";
    } elseif (mb_strlen($pseudo) < 3 || mb_strlen($pseudo) > 32) {
        $message = "⚠️ Le pseudo doit contenir entre 3 et 32 caractères.";
    } elseif ($password !== $confirm) {
        $message = "⚠️ Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $message = "⚠️ Le mot de passe doit contenir au moins 6 caractères.";
    } elseif (!$notabot) {
        $message = "⚠️ Veuillez cocher la case 'Je ne suis pas un bot'.";
    } elseif ($verif_mot !== "potiron") {
        $message = "⚠️ Mot de vérification incorrect.";
    } else {
        try {
    

            $user = new User();
            $user->pseudo   = $pseudo;
            $user->email    = $email;
            $user->password = $password;

            if ($user->insert()) {
                header("Location: login.php?success=1");
                exit;
            } else {
                $message = "❌ Erreur lors de la création du compte.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "⚠️ Pseudo ou email déjà utilisé.";
            } else {
                $message = "❌ Erreur SQL : " . $e->getMessage();
            }
        }
    }
}

/* =========
 * Données transmises à la vue (pré-échappées)
 * ========= */
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$started            = time(); // pour le timer honeypot
$message_safe       = $h($message);
$pseudo_safe        = $h($pseudo);
$email_safe         = $h($email);
$verif_mot_safe     = $h($verif_mot);
$checked_notabot    = isset($_POST['notabot']) ? 'checked' : '';

require "templates/formulaire_inscription.php";
