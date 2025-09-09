<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
 /* ====== CSS Login minimal ====== */
*{ box-sizing: border-box;
padding: 0;
margin: 0;
 }
html, body { height: 100%; }

body{
  margin: 0;
  font-family: system-ui, Arial, sans-serif;

  /* Fond */
  background-image: url("assets/images/farine-potiron.png");
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;

  /* Centre le formulaire et réserve de la place pour le bouton fixe */
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 72px 16px 16px; /* 72px en haut = espace sous le bouton .cta */
}

/* Lien "page d'accueil" (fixe en haut-gauche) */
.cta{
  position: fixed;
  top: 16px;
  left: 16px;
  display: flex;
  flex-direction: column;
}
.cta .btn{
  display: inline-block;
  padding: 8px 12px;
  background: #1f2937;
  color: #fff;
  text-decoration: none;
  border-radius: 999px;
  font-size: 14px;
}

/* Carte formulaire */
form{
  width: 100%;
  max-width: 360px;
  background: #fff;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
}

form h2{ margin: 0 0 12px; font-size: 22px; }
label{ display:block; font-size:14px; color:#374151; margin:6px 0; }

input[type="text"],
input[type="password"]{
  width: 100%;
  height: 42px;
  padding: 0 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
}
input:focus{ border-color:#2563eb; box-shadow:0 0 0 3px #2563eb22; }

button[type="submit"]{
  width: 100%;
  height: 44px;
  margin-top: 6px;
  border: 0;
  border-radius: 8px;
  cursor: pointer;
  background: #fbbf24;
  color: #111;
  font-weight: 700;
}
button[type="submit"]:hover{ filter: brightness(.95); }

/* Optionnel : message d'erreur PHP si tu enlèves le style inline */
.alert-error{
  margin: 10px 0 16px;
  padding: 10px 12px;
  background: #fff5f5;
  color: #b42318;
  border: 1px solid #ffdada;
  border-radius: 8px;
}

  </style>
</head>

<body>
  <div class="cta">
    <a class="btn" href="index.php">page d'acceuil</a>
  </div>
  <?php if (!empty($message)): ?>
    <div style="color:red; font-weight:bold;"><?= $message ?></div>
  <?php endif; ?>

  <form action="login.php" method="post">
    <h2>Connexion</h2>

    <label>Pseudo ou Email</label><br>
    <input type="text" name="identifiant" required><br><br>

    <label>Mot de passe</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Se connecter</button>
  </form>
</body>

</html>