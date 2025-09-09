<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Formulaire d'inscription</title>

  <!-- CSS critique pour cacher le honeypot même si le fichier externe ne charge pas -->
  <style>
    .hp-wrap{
      position:absolute!important;
      left:-10000px;top:auto;width:1px;height:1px;overflow:hidden
    }
  </style>

  <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>

  <div class="cta">
    <a class="btn" href="index.php">Page d’accueil</a>
  </div>

  <?php if (!empty($message_safe)): ?>
    <div class="alert alert--error"><?= $message_safe ?></div>
  <?php endif; ?>

  <form action="inscription.php" method="post" class="form" autocomplete="off" novalidate>
    <!-- Timer honeypot -->
    <input type="hidden" name="form_started" value="<?= (int)$started ?>">

    <!-- Champ honeypot (ne doit PAS être rempli) -->
    <div class="hp-wrap" aria-hidden="true">
      <label for="website">Ne pas remplir ce champ</label>
      <input type="text" name="website" id="website" tabindex="-1" autocomplete="off" />
    </div>

    <label for="pseudo">Pseudo</label>
    <input type="text" id="pseudo" name="pseudo" value="<?= $pseudo_safe ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= $email_safe ?>" required>

    <label for="password">Mot de passe</label>
    <input type="password" id="password" name="password" required>

    <label for="confirm_password">Confirmer le mot de passe</label>
    <input type="password" id="confirm_password" name="confirm_password" required>

    <label style="display:flex;align-items:center;gap:8px;margin-top:14px;">
      <input type="checkbox" name="notabot" <?= $checked_notabot ?>>
      <span>Je ne suis pas un bot</span>
    </label>

    <label for="verif_mot">Veuillez écrire le mot <strong>potiron</strong> pour valider</label>
    <input type="text" id="verif_mot" name="verif_mot" value="<?= $verif_mot_safe ?>" required>

    <button type="submit">Créer mon compte</button>
  </form>

</body>
</html>
