<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier mon profil</title>
</head>
<style>
  /* Fond (ta règle existante) */
body{
  background-image: url("../images/farine-potiron.png");
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  margin: 0;
  font-family: system-ui, Arial, sans-serif;
  color: #111;
  padding: 24px;           /* un peu d’air autour */
}

/* Titre */
h1{
  margin: 0 0 16px;
  text-align: center;
  color: #d81212ff;             /* lisible sur le fond */
  text-shadow: 0 1px 6px rgba(0,0,0,.35);
  font-size: 22px;
}

/* Formulaire (carte simple) */
form{
  width: 100%;
  max-width: 420px;
  margin: 0 auto;          /* centre horizontalement */
  background: rgba(255,255,255,.95);
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 16px;
}

/* Champs */
label{ display:block; font-size:14px; margin: 8px 0 6px; }
input[type="email"],
input[type="password"]{
  width: 100%;
  height: 40px;
  padding: 0 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
}
input:focus{ border-color:#2563eb; box-shadow:0 0 0 3px #2563eb22; }

/* Bouton */
button[type="submit"]{
  width: 100%;
  height: 42px;
  margin-top: 10px;
  border: 0;
  border-radius: 8px;
  cursor: pointer;
  background: #fbbf24;     /* jaune discret */
  color: #111;
  font-weight: 700;
}
button[type="submit"]:hover{ filter: brightness(.96); }

/* Lien retour */
p a{
  display: inline-block;
  margin: 12px auto 0;
  text-decoration: none;
  color: #fff;
  background: rgba(17,24,39,.75);
  padding: 6px 10px;
  border-radius: 8px;
}
p{ text-align: center; }

</style>
<body>
  <h1>Modifier mes informations</h1>

  <?php if (!empty($message)): ?>
    <div style="color:red;"><?= $message ?></div>
  <?php endif; ?>

  <form method="post" action="MajInfoProfil.php">
    <label>Email :</label><br>
    <input type="email" name="email" value="<?= $user->email ?>" required><br><br>

    <label>Nouveau mot de passe (laisser vide si inchangé) :</label><br>
    <input type="password" name="password"><br><br>

    <label>Confirmer le mot de passe :</label><br>
    <input type="password" name="confirm_password"><br><br>

    <button type="submit">Mettre à jour</button>
  </form>

  <!-- Retour passe par un contrôleur dédié -->
  <p><a href="profil_retour.php">⬅ Retour au profil</a></p>
</body>
</html>
