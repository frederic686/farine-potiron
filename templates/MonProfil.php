<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon profil</title>
  <style>
    body {
      background-image: url("assets/images/farine-potiron.png"); /* chemin vers ton image */
      background-size: cover;        /* l'image couvre tout l'écran */
      background-repeat: no-repeat;  /* pas de répétition */
      background-position: center;   /* centrée */
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 20px;
      text-align: center;
    }

    h1 {
      margin-bottom: 40px;
    }

    .box {
      border: 2px solid #000;
      padding: 20px;
      margin: 20px auto;
      width: 300px;
      background: #b3f7c3;
      font-size: 20px;
      font-weight: bold;
    }

    /* Style pour le bouton */
    .btn {
      display: block;
      width: 300px;
      margin: 20px auto;
      padding: 20px;
      background: #4caf50;
      color: white;
      border: none;
      font-size: 20px;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
      border-radius: 5px;
    }

    .btn:hover {
      background: #45a049;
    }
    .deconnexion {
      display: block;
      width: 300px;
      margin: 20px auto;
      padding: 20px;
      background: #dc310eff;
      color: white;
      border: none;
      font-size: 20px;
      font-weight: bold;
      cursor: pointer;
      text-decoration: none;
      border-radius: 5px;
    }

    .deconnexion:hover {
      background: #ff2a00ff;
    }


  </style>
</head>
<body>
<h1>Bienvenue <?= $user->pseudo ?> 👋</h1>


  <div class="box">
      <h2>Mon profil</h2>
  <ul>
    <li><strong>Pseudo :</strong> <?= $user->pseudo ?></li>
    <li><strong>Email :</strong> <?= $user->email ?></li>
  </ul>
  </div>
  <a href="MajInfoProfil.php" class="btn">modifie ton profil</a>

  <!-- Bouton menant vers recette.php -->
  <a href="MajRecette.php" class="btn">Mes recettes / Créer & modifier</a>

  <a href="recette.php" class="btn">voir les recette public</a>

  <p>
    <a href="deconnexion.php" class="deconnexion">Se déconnecter</a>
  </p>



</body>
</html>
