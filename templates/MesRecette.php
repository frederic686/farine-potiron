<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes recettes / Création & Édition</title>
  <style>
    body { font-family: Arial, sans-serif; margin:0; padding:20px; background:#f6f6f6;
      background-image: url("assets/images/farine-potiron.png");
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center;
    }
    h1 { margin-top:0; }
    .layout { display:flex; gap:20px; align-items:flex-start; }
    .list, .form { background:#fff; border:1px solid #ddd; border-radius:8px; padding:16px; }
    .list { width:45%; }
    .form { width:55%; }
    .item { padding:8px 0; border-bottom:1px dashed #ddd; }
    .item:last-child { border-bottom:none; }
    .meta { color:#666; font-size:13px; }
    .success { background:#e7ffe7; border:1px solid #80d480; padding:10px; border-radius:6px; margin-bottom:12px; }
    .error   { background:#ffecec; border:1px solid #ff8a8a; padding:10px; border-radius:6px; margin-bottom:12px; }
    fieldset { border:1px solid #ccc; padding:10px; border-radius:6px; }
    input[type="text"], input[type="number"], textarea, select {
      width:100%; padding:8px; margin:4px 0 10px; box-sizing:border-box;
    }
    .row { display:flex; gap:8px; align-items:center; }
    .row > *:not(.btn-remove) { flex:1; }
    .actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    button { padding:10px 16px; border:none; border-radius:6px; background:#4caf50; color:#fff; font-weight:bold; cursor:pointer; }
    a { color:#0b66c3; text-decoration:none; }
    a:hover { text-decoration:underline; }

    .btn-modif {
      display: inline-block;
      padding: 6px 12px;
      background: #4CAF50;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      font-size: 14px;
    }
    .btn-modif:hover { background: #45a049; }

    .btn-secondary{
      background:#0b66c3;
    }
    .btn-secondary:hover{ background:#094a8f; }

    .btn-remove{
      background:#C0392B;
      color:#fff;
      border:none;
      border-radius:6px;
      padding:8px 10px;
      cursor:pointer;
      white-space:nowrap;
    }
    .btn-remove:hover{ background:#962d22; }

    /* Accordéon détails */
    .details { overflow: hidden; max-height: 0; transition: max-height 0.25s ease; }
    .details.open { max-height: 600px; }
    .details .content {
      padding: 10px 0 0; color: #333; line-height: 1.5;
      border-top: 1px dashed #ddd; margin-top: 8px; white-space: pre-wrap;
    }
    .btn-detail {
      display: inline-block; padding: 6px 12px; background: #0b66c3; color: #fff;
      text-decoration: none; border-radius: 6px; font-size: 14px; cursor: pointer;
    }
    .btn-detail:hover { background: #094a8f; }
  </style>
</head>
<body>
  <h1>Mes recettes</h1>
    <p style="margin-top:20px;">
      <a href="profil_retour.php" class="btn-modif">⬅ Retour au profil</a>
    </p>

  <?php if (!empty($success)): ?>
    <div class="success">
      <?php
        echo $success==='created' ? "✅ Recette créée."
           : ($success==='updated' ? "✅ Recette mise à jour."
           : ($success==='deleted' ? "✅ Recette supprimée." : ""));
      ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($message)): ?>
    <div class="error"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="layout">

    <!-- Colonne gauche : liste -->
    <div class="list">
      <h2>Mes recettes publiées</h2>
      <p><a href="MajRecette.php">+ Nouvelle recette</a></p>

      <?php if (empty($recettes)): ?>
        <p>Aucune recette pour l’instant.</p>
      <?php else: ?>
        <?php foreach ($recettes as $r): ?>
          <div class="item">
            <div>
              <h2><?= htmlspecialchars($r['titre']) ?></h2>
            </div>
            <div class="meta">
              <?= htmlspecialchars($r['difficulte']) ?> —
              <?= (int)$r['duree'] ?> min —
              créé le <?= htmlspecialchars($r['date_creation']) ?>
            </div>

            <div style="margin-top:8px; display:flex; gap:10px;">
              <a href="MajRecette.php?id=<?= (int)$r['id'] ?>" class="btn-modif">✎ Modifier</a>
              <button type="button" class="btn-detail btn-secondary" data-target="details-<?= (int)$r['id'] ?>">
                👁 Voir détail
              </button>
            </div>

            <!-- Bloc détails caché -->
            <div id="details-<?= (int)$r['id'] ?>" class="details">
              <div class="content">

                <!-- Description -->
                <h3>Description</h3>
                <?php if (!empty($r['description'])): ?>
                  <p><?= nl2br(htmlspecialchars($r['description'])) ?></p>
                <?php else: ?>
                  <em>Aucune description enregistrée.</em>
                <?php endif; ?>

                <!-- Ingrédients -->
                <h3>Ingrédients</h3>
                <?php if (!empty($r['ingredients'])): ?>
                  <ul>
                    <?php foreach ($r['ingredients'] as $ing): ?>
                      <li>
                        <?php
                          // Libellé : farine depuis catalogue si ref_farine, sinon nom libre
                          $lib = '';
                          if (!empty($ing['ref_farine']) && isset($catalogueFarines[$ing['ref_farine']])) {
                            $lib = $catalogueFarines[$ing['ref_farine']];
                          } elseif (!empty($ing['nom'])) {
                            $lib = $ing['nom'];
                          } else {
                            $lib = 'Ingrédient';
                          }
                          echo htmlspecialchars($lib);
                        ?>
                        — <strong><?= htmlspecialchars($ing['quantite'] ?? '') ?></strong>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <em>Aucun ingrédient enregistré.</em>
                <?php endif; ?>

              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <!-- /Colonne gauche -->

    <!-- Colonne droite : formulaire (création / édition / suppression via case) -->
    <div class="form">
      <h2><?= !empty($recette['id']) ? 'Modifier la recette' : 'Créer une recette' ?></h2>

      <form id="form-recette" method="post" action="MajRecette.php">
        <input type="hidden" name="id" value="<?= (int)($recette['id'] ?? 0) ?>">

        <label>Titre</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($recette['titre'] ?? '') ?>" required>

        <label>Description</label>
        <textarea name="description" rows="5" required><?= htmlspecialchars($recette['description'] ?? '') ?></textarea>

        <div class="row">
          <div>
            <label>Durée (minutes)</label>
            <input type="number" name="duree" min="1" value="<?= (int)($recette['duree'] ?? 0) ?>" required>
          </div>
          <div>
            <label>Difficulté</label>
            <select name="difficulte" required>
              <?php
                $opts = ['très facile','facile','difficile'];
                $val  = $recette['difficulte'] ?? '';
                foreach ($opts as $opt) {
                  $sel = ($opt === $val) ? 'selected' : '';
                  echo "<option value=\"".htmlspecialchars($opt)."\" $sel>".htmlspecialchars($opt)."</option>";
                }
              ?>
            </select>
          </div>
        </div>

        <fieldset>
          <legend>Ingrédients</legend>
          <p style="font-size:13px;color:#666;margin-top:0;">
            Choisissez une farine F&P <em>ou</em> saisissez un autre ingrédient. (Au moins une farine est requise)
          </p>

          <!-- Conteneur des lignes -->
          <div id="ingredients-container">
            <?php if (!empty($ingredients)): ?>
              <?php foreach ($ingredients as $ing): ?>
                <div class="row ingredient-line">
                  <!-- Farine F&P depuis l’API -->
                  <select name="ing_farine[]">
                    <option value="">— Choisir une farine F&P —</option>
                    <?php foreach ($catalogueFarines as $ref => $lib): ?>
                      <option value="<?= htmlspecialchars($ref) ?>"
                        <?= (!empty($ing['ref_farine']) && $ing['ref_farine'] == $ref) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lib) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>

                  <!-- Autre ingrédient libre -->
                  <input type="text" name="ing_nom[]" placeholder="Autre ingrédient"
                        value="<?= htmlspecialchars($ing['nom'] ?? '') ?>">

                  <!-- Quantité -->
                  <input type="text" name="ing_qte[]" placeholder="Quantité"
                        value="<?= htmlspecialchars($ing['quantite'] ?? '') ?>">

                  <button type="button" class="btn-remove" title="Supprimer cette ligne">❌</button>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Bouton pour ajouter dynamiquement -->
          <div class="actions" style="margin-top:8px">
            <button type="button" id="btn-add" class="btn-secondary">➕ Ajouter un ingrédient</button>
          </div>

          <!-- Template (cloné en JS) -->
          <template id="tpl-ingredient">
            <div class="row ingredient-line">
              <select name="ing_farine[]">
                <option value="">— Choisir une farine F&P —</option>
                <?php foreach ($catalogueFarines as $ref => $lib): ?>
                  <option value="<?= htmlspecialchars($ref) ?>"><?= htmlspecialchars($lib) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="text" name="ing_nom[]" placeholder="Autre ingrédient">
              <input type="text" name="ing_qte[]" placeholder="Quantité">
              <button type="button" class="btn-remove" title="Supprimer cette ligne">❌</button>
            </div>
          </template>
        </fieldset>

        <div class="actions" style="margin-top:10px;">
          <?php if (!empty($recette['id'])): ?>
            <label style="display:flex;align-items:center;gap:6px;">
              <input type="checkbox" name="supprimer"> Supprimer cette recette
            </label>
          <?php endif; ?>

          <button type="submit">Enregistrer</button>
        </div>
      </form>
    </div>
    <!-- /Colonne droite -->

  </div>

  <!-- Accordéon JS + Ingrédients dynamiques + Validation -->
  <script>
    // -------- Accordéon (liste à gauche)
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-detail');
      if (!btn) return;

      const panel = document.getElementById(btn.getAttribute('data-target'));
      if (!panel) return;

      const isOpen = panel.classList.contains('open');
      if (isOpen) {
        panel.style.maxHeight = panel.scrollHeight + 'px';
        requestAnimationFrame(() => {
          panel.classList.remove('open');
          panel.style.maxHeight = '0';
        });
        btn.textContent = '👁 Voir détail';
      } else {
        panel.classList.add('open');
        panel.style.maxHeight = panel.scrollHeight + 'px';
        btn.textContent = '👁 Masquer détail';
        panel.addEventListener('transitionend', function t() {
          panel.style.maxHeight = 'none';
          panel.removeEventListener('transitionend', t);
        });
      }
    });

    // -------- Ingrédients dynamiques
    (function(){
      const container = document.getElementById('ingredients-container');
      const tpl = document.getElementById('tpl-ingredient');
      const btnAdd = document.getElementById('btn-add');

      // Ajoute une ligne
      function addLine(focus = true){
        const node = document.importNode(tpl.content, true);
        const line = node.querySelector('.ingredient-line');

        // Bouton supprimer
        line.querySelector('.btn-remove').addEventListener('click', () => {
          // On garde au moins une ligne visible
          if (container.querySelectorAll('.ingredient-line').length > 1) {
            line.remove();
          } else {
            // Si c'est la dernière ligne, on la "réinitialise"
            line.querySelector('select[name="ing_farine[]"]').value = "";
            line.querySelector('input[name="ing_nom[]"]').value = "";
            line.querySelector('input[name="ing_qte[]"]').value = "";
          }
        });

        container.appendChild(line);
        if (focus) {
          const select = line.querySelector('select[name="ing_farine[]"]');
          if (select) select.focus();
        }
      }

      // Si aucune ligne n’a été rendue côté PHP (nouvelle recette), on en crée une
      if (!container.querySelector('.ingredient-line')) {
        addLine(false);
      } else {
        // On attache le remove aux lignes existantes
        container.querySelectorAll('.ingredient-line .btn-remove').forEach(btn => {
          btn.addEventListener('click', (ev) => {
            const line = ev.target.closest('.ingredient-line');
            if (container.querySelectorAll('.ingredient-line').length > 1) {
              line.remove();
            } else {
              line.querySelector('select[name="ing_farine[]"]').value = "";
              line.querySelector('input[name="ing_nom[]"]').value = "";
              line.querySelector('input[name="ing_qte[]"]').value = "";
            }
          });
        });
      }

      btnAdd.addEventListener('click', () => addLine(true));

      // -------- Validation minimale : au moins une farine F&P sélectionnée
      const form = document.getElementById('form-recette');
      form.addEventListener('submit', (e) => {
        const selects = container.querySelectorAll('select[name="ing_farine[]"]');
        let hasFlour = false;
        selects.forEach(s => { if (s.value && s.value.trim() !== '') hasFlour = true; });
        if (!hasFlour) {
          e.preventDefault();
          alert("Veuillez sélectionner au moins une farine F&P dans la liste déroulante.");
        }
      });
    })();
  </script>
</body>
</html>
