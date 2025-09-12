<?php
/**
 * Vue: accueil
 * Rôle: Afficher la page d’accueil avec accès rapide aux actions principales.
 * Sortie: HTML
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farine & Potiron</title>

  <!-- Lien vers ton fichier CSS -->
  
  <link rel="stylesheet" href="assets/css/styles.css">

</head>
<body>
  <div class="home">
    <section class="hero">
      <h1>Farine &amp; Potiron</h1>
      <p>Partagez et découvrez des recettes à base de nos farines.</p>

      <div class="cta">
        <a class="btn" href="login.php">Se connecter</a>
        <a class="btn" href="inscription.php">S'inscrire</a>
        <a class="btn" href="recette.php">Accès aux recettes</a>
      </div>
    </section>
  </div>
</body>
</html>
