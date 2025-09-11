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

    .btn-modif { display:inline-block; padding:6px 12px; background:#4CAF50; color:#fff; text-decoration:none; border-radius:6px; font-size:14px; }
    .btn-modif:hover { background:#45a049; }

    .btn-secondary{ background:#0b66c3; }
    .btn-secondary:hover{ background:#094a8f; }

    .btn-remove{ background:#C0392B; color:#fff; border:none; border-radius:6px; padding:8px 10px; cursor:pointer; white-space:nowrap; }
    .btn-remove:hover{ background:#962d22; }

    /* Accordéon détails */
    .details { overflow: hidden; max-height: 0; transition: max-height 0.25s ease; }
    .details.open { max-height: 600px; }
    .details .content {
      padding: 10px 0 0; color: #333; line-height: 1.5;
      border-top: 1px dashed #ddd; margin-top: 8px; white-space: pre-wrap;
    }
    .btn-detail { display:inline-block; padding:6px 12px; background:#0b66c3; color:#fff; text-decoration:none; border-radius:6px; font-size:14px; cursor:pointer; }
    .btn-detail:hover { background:#094a8f; }
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

              <!-- 🔧 bouton suppression AJAX (liste) -->
              <button type="button" class="btn-remove js-del" data-id="<?= (int)$r['id'] ?>">🗑 Supprimer</button>

              <button type="button" class="btn-detail btn-secondary" data-target="details-<?= (int)$r['id'] ?>">
                👁 Voir détail
              </button>
            </div>

            <!-- Bloc détails caché -->
            <div id="details-<?= (int)$r['id'] ?>" class="details">
              <div class="content">
                <h3>Description</h3>
                <?php if (!empty($r['description'])): ?>
                  <p><?= nl2br(htmlspecialchars($r['description'])) ?></p>
                <?php else: ?>
                  <em>Aucune description enregistrée.</em>
                <?php endif; ?>

                <h3>Ingrédients</h3>
                <?php if (!empty($r['ingredients'])): ?>
                  <ul>
                    <?php foreach ($r['ingredients'] as $ing): ?>
                      <li>
                        <?php
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

    <!-- Colonne droite : formulaire (création / édition AJAX) -->
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

          <div id="ingredients-container">
            <?php if (!empty($ingredients)): ?>
              <?php foreach ($ingredients as $ing): ?>
                <div class="row ingredient-line">
                  <select name="ing_farine[]">
                    <option value="">— Choisir une farine F&P —</option>
                    <?php foreach ($catalogueFarines as $ref => $lib): ?>
                      <option value="<?= htmlspecialchars($ref) ?>"
                        <?= (!empty($ing['ref_farine']) && $ing['ref_farine'] == $ref) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lib) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>

                  <input type="text" name="ing_nom[]" placeholder="Autre ingrédient"
                         value="<?= htmlspecialchars($ing['nom'] ?? '') ?>">

                  <input type="text" name="ing_qte[]" placeholder="Quantité"
                         value="<?= htmlspecialchars($ing['quantite'] ?? '') ?>">

                  <button type="button" class="btn-remove" title="Supprimer cette ligne">❌</button>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <div class="actions" style="margin-top:8px">
            <button type="button" id="btn-add" class="btn-secondary">➕ Ajouter un ingrédient</button>
          </div>

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
            <!-- 🔧 bouton suppression AJAX (formulaire) -->
            <button type="button" id="btn-delete" class="btn-remove">Supprimer</button>
          <?php endif; ?>
          <button type="submit">Enregistrer</button>
        </div>
      </form>
    </div>
    <!-- /Colonne droite -->

  </div>

  <!-- Accordéon + Ingrédients dynamiques + AJAX -->
  <script>
    // Accordéon (liste à gauche)
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

    // Ingrédients dynamiques
    (function(){
      const container = document.getElementById('ingredients-container');
      const tpl = document.getElementById('tpl-ingredient');
      const btnAdd = document.getElementById('btn-add');

      function wireRemove(btn, line){
        btn.addEventListener('click', () => {
          // Garde au moins une ligne
          if (container.querySelectorAll('.ingredient-line').length > 1) {
            line.remove();
          } else {
            line.querySelector('select[name="ing_farine[]"]').value = "";
            line.querySelector('input[name="ing_nom[]"]').value = "";
            line.querySelector('input[name="ing_qte[]"]').value = "";
          }
        });
      }

      function addLine(focus = true){
        const node = document.importNode(tpl.content, true);
        const line = node.querySelector('.ingredient-line');
        wireRemove(line.querySelector('.btn-remove'), line);
        container.appendChild(line);
        if (focus) line.querySelector('select[name="ing_farine[]"]').focus();
      }

      if (!container.querySelector('.ingredient-line')) {
        addLine(false);
      } else {
        container.querySelectorAll('.ingredient-line').forEach(line => {
          wireRemove(line.querySelector('.btn-remove'), line);
        });
      }
      btnAdd.addEventListener('click', () => addLine(true));
    })();

    // ===== AJAX (save + delete)
    (function(){
      const form = document.getElementById('form-recette');
      const container = document.getElementById('ingredients-container');

      function hasAtLeastOneFlour(){
        const selects = container.querySelectorAll('select[name="ing_farine[]"]');
        for (const s of selects) { if (s.value && s.value.trim() !== '') return true; }
        return false;
      }

      // SAVE (create/update)
      form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validation légère (côté serveur aussi)
        if (!hasAtLeastOneFlour()) {
          alert("Veuillez sélectionner au moins une farine F&P dans la liste déroulante.");
          return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
          const fd = new FormData(form);
          fd.set('action', 'save');

          const res = await fetch('MajRecette.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
          });
          const json = await res.json();

          if (json.ok) {
            const flag = (json.msg === 'Créée') ? 'created' : 'updated';
            location.href = 'MajRecette.php?id=' + json.id + '&success=' + flag;
          } else {
            alert('❌ ' + (json.msg || 'Erreur'));
          }
        } catch (err) {
          console.error(err);
          alert('❌ Erreur réseau');
        } finally {
          submitBtn.disabled = false;
        }
      });

      // DELETE (depuis le formulaire)
      document.getElementById('btn-delete')?.addEventListener('click', async () => {
        const id = parseInt(document.querySelector('input[name="id"]').value || '0', 10);
        if (!id) { alert('Pas d’ID.'); return; }
        if (!confirm('Supprimer définitivement cette recette ?')) return;

        const fd = new FormData();
        fd.set('action', 'delete');
        fd.set('id', String(id));

        try {
          const res = await fetch('MajRecette.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
          });
          const json = await res.json();
          if (json.ok) {
            location.href = 'MajRecette.php?success=deleted';
          } else {
            alert('❌ ' + (json.msg || 'Suppression impossible'));
          }
        } catch (err) {
          console.error(err);
          alert('❌ Erreur réseau');
        }
      });

      // DELETE (depuis la liste)
      document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-del');
        if (!btn) return;

        const id = parseInt(btn.dataset.id || '0', 10);
        if (!id) return;
        if (!confirm('Supprimer définitivement cette recette ?')) return;

        const fd = new FormData();
        fd.set('action', 'delete');
        fd.set('id', String(id));

        btn.disabled = true;
        try {
          const res = await fetch('MajRecette.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
          });
          const json = await res.json();
          if (json.ok) {
            const item = btn.closest('.item');
            if (item) item.remove();
          } else {
            alert('❌ ' + (json.msg || 'Suppression impossible'));
          }
        } catch (err) {
          console.error(err);
          alert('❌ Erreur réseau');
        } finally {
          btn.disabled = false;
        }
      });
    })();
  </script>
</body>
</html>
