<?php
// templates/recette_public.php
// Variables attendues :
// - $recettes : [id, titre, description, duree, difficulte, date_creation, auteur_pseudo,
//                ingredients[], commentaires[], moyenne_note, nb_notes,
//                can_comment, own_note, own_comment_id, own_comment_texte, own_comment_date, is_edit]
// - $catalogueFarines : [ref => libellé]  // (facultatif ici)
// - $filters : ['q']                      // (facultatif ici)
// - $isConnected, $redirectToLogin, $CSRF, $info, $error

$currentUserId    = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$filters          = is_array($filters ?? null) ? $filters : [];
$q                = htmlspecialchars($filters['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Recettes – Farine & Potiron</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body { font-family: Arial, sans-serif; margin:0; padding:24px; background:#f6f6f6; }
    .container { max-width: 1000px; margin: 0 auto; }
    h1 { margin: 0 0 16px; }
    .msg { margin-bottom:12px; padding:10px; border-radius:8px; }
    .msg.info { background:#e7ffe7; border:1px solid #80d480; }
    .msg.error { background:#ffecec; border:1px solid #ff8a8a; }

    .card { background:#fff; border:1px solid #ddd; border-radius:10px; padding:16px; margin-bottom:16px; }
    .meta { color:#666; font-size:13px; margin:6px 0 12px; }
    .ingredients ul { padding-left: 18px; margin: 8px 0; }
    .rating { font-weight:600; }
    .comments { margin-top:12px; }
    .comment { border-top: 1px dashed #e1e1e1; padding-top:10px; margin-top:10px; }
    .comment small { color:#666; }
    .muted { color:#777; font-size:12px; }
    .btn { display:inline-block; padding:8px 12px; border:2px solid #111; border-radius:10px; text-decoration:none; color:#111; background:#fff; font-weight:600; }
    .btn:hover { transform: translateY(-1px); }
    .btn-mini { font-size:12px; padding:6px 8px; border:1px solid #333; border-radius:8px; background:#fff; cursor:pointer; }
    .btn-mini:hover { transform: translateY(-1px); }

    .inline-edit { display:none; margin-top:8px; padding:10px; border:1px solid #ddd; border-radius:8px; background:#fafafa; }
    .inline-edit textarea { width:100%; min-height:90px; padding:8px; }
    .inline-edit .row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:6px 0; }

    /* Barre de recherche simple */
    .search { background:#fff; border:1px solid #ddd; border-radius:10px; padding:12px; margin:0 0 16px; }
    .search .row { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
    .search label { font-size:12px; color:#555; display:block; margin-bottom:4px; }
    .search input[type="text"] { padding:8px; border:1px solid #ccc; border-radius:8px; min-width:260px; }
    .search .actions { display:flex; gap:8px; align-items:center; }
    .reset-link { font-size:12px; color:#666; text-decoration:none; }
  </style>
</head>
<body>
  <div class="cta" style="display:flex; gap:8px; margin-bottom:12px;">
    <a class="btn" href="index.php">page d'acceuil</a>
    <a href="deconnexion.php" class="btn">Se déconnecter</a>
    <a href="profil_retour.php" class="btn">⬅ Retour au profil</a>
  </div>

  <div class="container">
    <h1>Toutes les recettes</h1>

    <!-- Barre de recherche (mot-clé) -> envoi vers ton contrôleur note_commentaire.php -->
    <form class="search" method="get" action="note_commentaire.php">
      <div class="row">
        <div>
          <label for="q">Mot-clé</label>
          <input id="q" type="text" name="q" value="<?= $q ?>" placeholder="Titre, description…">
        </div>
        <div class="actions">
          <button type="submit" class="btn">Rechercher</button>
          <a class="reset-link" href="recette.php">Réinitialiser</a>
        </div>
      </div>
    </form>

    <?php if (!empty($info)): ?>
      <div class="msg info"><?= htmlspecialchars($info) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="msg error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($recettes)): ?>
      <p>Aucune recette pour le moment.</p>
    <?php else: ?>
      <?php foreach ($recettes as $r): ?>
        <article class="card" id="recette-<?= (int)$r['id'] ?>">
          <header>
            <h2><?= htmlspecialchars($r['titre']) ?></h2>
            <div class="meta">
              Difficulté : <?= htmlspecialchars($r['difficulte']) ?> —
              Durée : <?= (int)$r['duree'] ?> min —
              Publiée le <?= htmlspecialchars($r['date_creation']) ?> —
              <strong>par <?= htmlspecialchars($r['auteur_pseudo'] ?? 'Anonyme') ?></strong>
            </div>
          </header>

          <section class="description">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($r['description'])) ?></p>
          </section>

          <section class="ingredients">
            <h3>Ingrédients</h3>
            <?php if (!empty($r['ingredients'])): ?>
              <ul>
                <?php foreach ($r['ingredients'] as $ing): ?>
                  <li>
                    <?= htmlspecialchars($ing['nom'] ?? 'Ingrédient') ?>
                    <?php if (!empty($ing['quantite'])): ?>
                      — <strong><?= htmlspecialchars($ing['quantite']) ?></strong>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <em>Aucun ingrédient enregistré.</em>
            <?php endif; ?>
          </section>

          <section class="notes">
            <h3>Notes & Commentaires</h3>
            <p class="rating">
              Note moyenne :
              <?php if (!empty($r['nb_notes'])): ?>
                <?= number_format((float)$r['moyenne_note'], 1, ',', ' ') ?>/5 (<?= (int)$r['nb_notes'] ?> note<?= $r['nb_notes']>1?'s':'' ?>)
              <?php else: ?>
                Aucune note pour l’instant
              <?php endif; ?>
            </p>

            <div class="comments">
              <?php if (!empty($r['commentaires'])): ?>
                <?php foreach ($r['commentaires'] as $c): ?>
                  <?php
                    $updated    = isset($c['date_update']) && $c['date_update'] && $c['date_update'] !== $c['date_creation'];
                    $commentId  = (int)($c['id'] ?? 0);
                    $ownComment = $currentUserId > 0 && isset($c['id_utilisateur']) && (int)$c['id_utilisateur'] === $currentUserId;
                    $noteAct    = (int)($c['note'] ?? 0);
                  ?>
                  <div class="comment" id="comment-<?= $commentId ?>">
                    <div>
                      <strong><?= htmlspecialchars($c['pseudo'] ?? 'Anonyme') ?></strong>
                      <?php if (isset($c['note']) && $c['note'] !== null): ?>
                        — note: <?= (int)$c['note'] ?>/5
                      <?php endif; ?>
                    </div>

                    <p>
                      <?php if (isset($c['texte']) && trim((string)$c['texte']) !== ''): ?>
                        <?= nl2br(htmlspecialchars($c['texte'])) ?>
                      <?php else: ?>
                        <em>(Sans commentaire)</em>
                      <?php endif; ?>
                    </p>

                    <small class="muted">
                      <?= $updated ? 'modifié le ' . htmlspecialchars($c['date_update']) : 'publié le ' . htmlspecialchars($c['date_creation']) ?>
                    </small>

                    <?php if ($ownComment): ?>
                      <!-- Ouvre/ferme le mini-form d’édition -->
                      <div style="margin-top:6px;">
                        <button type="button"
                                class="btn-mini toggle-edit"
                                data-target="#edit-<?= (int)$r['id'] ?>-<?= $commentId ?>">
                          ✏️ Mettre à jour
                        </button>
                      </div>

                      <!-- Mini-formulaire d’édition -->
                      <form method="post"
                            action="note_commentaire.php"
                            class="inline-edit"
                            id="edit-<?= (int)$r['id'] ?>-<?= $commentId ?>">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($CSRF) ?>">
                        <input type="hidden" name="id_recette" value="<?= (int)$r['id'] ?>">
                        <input type="hidden" name="action" value="editer">
                        <input type="hidden" name="comment_id" value="<?= $commentId ?>">
                        <input type="hidden" name="redirect" value="recette.php">

                        <div class="row">
                          <label for="note-edit-<?= $commentId ?>"><strong>Votre note</strong></label>
                          <select id="note-edit-<?= $commentId ?>" name="note">
                            <?php for ($i=0; $i<=5; $i++): ?>
                              <option value="<?= $i ?>" <?= $i===$noteAct?'selected':'' ?>>
                                <?= $i===0 ? '— Pas de note —' : ($i.' / 5') ?>
                              </option>
                            <?php endfor; ?>
                          </select>
                        </div>

                        <div>
                          <label for="texte-edit-<?= $commentId ?>"><strong>Votre commentaire</strong></label>
                          <textarea id="texte-edit-<?= $commentId ?>" name="texte" placeholder="Mettez à jour votre avis..."><?= htmlspecialchars((string)($c['texte'] ?? '')) ?></textarea>
                        </div>

                        <div class="row" style="margin-top:8px;">
                          <button type="submit" class="btn-mini">💾 Enregistrer</button>
                          <button type="button" class="btn-mini toggle-edit" data-target="#edit-<?= (int)$r['id'] ?>-<?= $commentId ?>">Annuler</button>
                        </div>
                      </form>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <em>Aucun commentaire pour l’instant.</em>
              <?php endif; ?>
            </div>

            <?php if ($isConnected && !empty($r['can_comment'])): ?>
              <?php if (empty($r['is_edit'])): ?>
                <!-- L'utilisateur n'a PAS encore réagi : mini-form de création -->
                <div style="margin-top:10px;">
                  <button type="button" class="btn-mini toggle-edit" data-target="#new-<?= (int)$r['id'] ?>">➕ Ajouter un commentaire / une note</button>
                </div>

                <form method="post"
                      action="note_commentaire.php"
                      class="inline-edit"
                      id="new-<?= (int)$r['id'] ?>">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($CSRF) ?>">
                  <input type="hidden" name="id_recette" value="<?= (int)$r['id'] ?>">
                  <input type="hidden" name="action" value="commenter">
                  <input type="hidden" name="redirect" value="recette.php">

                  <div class="row">
                    <label for="note-new-<?= (int)$r['id'] ?>"><strong>Votre note</strong></label>
                    <select id="note-new-<?= (int)$r['id'] ?>" name="note">
                      <?php for ($i=0; $i<=5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i===0 ? '— Pas de note —' : ($i.' / 5') ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>

                  <div>
                    <label for="texte-new-<?= (int)$r['id'] ?>"><strong>Votre commentaire</strong></label>
                    <textarea id="texte-new-<?= (int)$r['id'] ?>" name="texte" placeholder="Partagez votre avis..."></textarea>
                  </div>

                  <div class="row" style="margin-top:8px;">
                    <button type="submit" class="btn-mini">Publier</button>
                    <button type="button" class="btn-mini toggle-edit" data-target="#new-<?= (int)$r['id'] ?>">Annuler</button>
                  </div>
                </form>
              <?php endif; ?>
            <?php elseif (!$isConnected): ?>
              <a class="btn-mini" href="<?= htmlspecialchars($redirectToLogin) ?>">Se connecter pour commenter / noter</a>
            <?php endif; ?>

          </section>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <script>
    // Affiche/masque les mini-formulaires (édition ou création)
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.toggle-edit');
      if (!btn) return;
      const sel = btn.getAttribute('data-target');
      if (!sel) return;
      const el = document.querySelector(sel);
      if (!el) return;
      el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
    });
  </script>
</body>
</html>
