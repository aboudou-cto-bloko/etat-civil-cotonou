<?php
$isEdit = !empty($acte['id']);
$v      = fn($field) => \App\Core\View::e($acte[$field] ?? '');
$err    = fn($field) => !empty($errors[$field]) ? '<div class="form-error">' . \App\Core\View::e($errors[$field]) . '</div>' : '';
$fClass = fn($field) => !empty($errors[$field]) ? ' form-control--error' : '';
$user   = $_SESSION['user'] ?? [];
?>

<div class="breadcrumb">
  <a href="/naissances">Naissances</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-current"><?= $isEdit ? 'Modifier' : 'Nouveau acte' ?></span>
</div>

<div class="page-header">
  <h1 class="page-title"><?= $isEdit ? 'Modifier l\'acte de naissance' : 'Nouveau acte de naissance' ?></h1>
  <p class="page-subtitle">
    <?= $isEdit
      ? 'Modifiez les informations. La modification est tracée dans le journal d\'audit.'
      : 'Saisir les informations conformément à l\'Ordonnance n°69-23 du 10 juillet 1969.' ?>
  </p>
</div>

<form method="POST" action="<?= $isEdit ? '/naissances/' . $v('id') . '/modifier' : '/naissances' ?>">
  <?= \App\Core\View::csrfField() ?>

  <!-- SECTION : ARRONDISSEMENT & DATE -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Registre</div>
    <div class="form-grid">

      <div class="form-group">
        <label class="form-label form-label-required" for="arrondissement_id">Arrondissement</label>
        <?php if (!empty($user['arrondissement_id'])): ?>
          <input class="form-control" type="text" value="<?= \App\Core\View::e($user['arrondissement_nom'] ?? '') ?>" disabled>
          <input type="hidden" name="arrondissement_id" value="<?= \App\Core\View::e($user['arrondissement_id']) ?>">
        <?php else: ?>
          <select class="form-control<?= $fClass('arrondissement_id') ?>" name="arrondissement_id" id="arrondissement_id" required>
            <option value="">Sélectionner un arrondissement</option>
            <?php foreach ($arrondissements as $arr): ?>
            <option value="<?= $arr['id'] ?>" <?= ($acte['arrondissement_id'] ?? '') == $arr['id'] ? 'selected' : '' ?>>
              <?= \App\Core\View::e($arr['nom']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
        <?= $err('arrondissement_id') ?>
      </div>

      <div class="form-group">
        <label class="form-label form-label-required" for="date_declaration">Date de déclaration</label>
        <input class="form-control<?= $fClass('date_declaration') ?>" type="date" id="date_declaration" name="date_declaration"
               value="<?= $v('date_declaration') ?: date('Y-m-d') ?>" required>
        <?= $err('date_declaration') ?>
      </div>

    </div>
  </div>

  <!-- SECTION : ENFANT -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Informations sur l'enfant</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="enfant_nom">Nom de famille</label>
          <input class="form-control<?= $fClass('enfant_nom') ?>" type="text" id="enfant_nom" name="enfant_nom"
                 value="<?= $v('enfant_nom') ?>" required placeholder="Nom de famille" style="text-transform:uppercase;">
          <?= $err('enfant_nom') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="enfant_prenom">Prénom(s)</label>
          <input class="form-control<?= $fClass('enfant_prenom') ?>" type="text" id="enfant_prenom" name="enfant_prenom"
                 value="<?= $v('enfant_prenom') ?>" required placeholder="Prénom(s) de l'enfant">
          <?= $err('enfant_prenom') ?>
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label form-label-required" for="enfant_sexe">Sexe</label>
          <select class="form-control<?= $fClass('enfant_sexe') ?>" id="enfant_sexe" name="enfant_sexe" required>
            <option value="">—</option>
            <option value="M" <?= $v('enfant_sexe') === 'M' ? 'selected' : '' ?>>Masculin</option>
            <option value="F" <?= $v('enfant_sexe') === 'F' ? 'selected' : '' ?>>Féminin</option>
          </select>
          <?= $err('enfant_sexe') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="date_naissance">Date et heure de naissance</label>
          <input class="form-control<?= $fClass('date_naissance') ?>" type="datetime-local" id="date_naissance" name="date_naissance"
                 value="<?= $v('date_naissance') ?>" required>
          <?= $err('date_naissance') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="lieu_naissance_commune">Commune de naissance</label>
          <input class="form-control<?= $fClass('lieu_naissance_commune') ?>" type="text" id="lieu_naissance_commune"
                 name="lieu_naissance_commune" value="<?= $v('lieu_naissance_commune') ?>" required placeholder="Ex: Cotonou">
          <?= $err('lieu_naissance_commune') ?>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="lieu_naissance_localite">Quartier / Localité</label>
          <input class="form-control" type="text" id="lieu_naissance_localite" name="lieu_naissance_localite"
                 value="<?= $v('lieu_naissance_localite') ?>" placeholder="Ex: Akpakpa">
        </div>
        <div class="form-group" style="display:flex;gap:var(--space-7);">
          <div style="flex:1;">
            <label class="form-label" for="enfant_est_jumeau">Naissance multiple</label>
            <div class="form-check" style="margin-top:10px;">
              <input type="checkbox" id="enfant_est_jumeau" name="enfant_est_jumeau" value="1"
                     <?= ($acte['enfant_est_jumeau'] ?? 0) ? 'checked' : '' ?>>
              <label class="form-check-label" for="enfant_est_jumeau">Cet enfant est jumeau / multiple</label>
            </div>
          </div>
          <div style="flex:1;">
            <label class="form-label" for="ordre_jumeau">Ordre de naissance</label>
            <input class="form-control" type="number" id="ordre_jumeau" name="ordre_jumeau"
                   value="<?= $v('ordre_jumeau') ?>" min="1" max="9" placeholder="1, 2, 3...">
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- SECTION : PÈRE -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Informations sur le père</div>
    <div class="form-row">

      <div class="form-group">
        <label class="form-label" for="pere_statut">Statut du père</label>
        <select class="form-control" id="pere_statut" name="pere_statut">
          <option value="CONNU"   <?= $v('pere_statut') === 'CONNU'   ? 'selected' : '' ?>>Connu</option>
          <option value="INCONNU" <?= $v('pere_statut') === 'INCONNU' ? 'selected' : '' ?>>Inconnu</option>
          <option value="DÉCÉDÉ"  <?= $v('pere_statut') === 'DÉCÉDÉ'  ? 'selected' : '' ?>>Décédé</option>
        </select>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="pere_nom">Nom de famille</label>
          <input class="form-control" type="text" id="pere_nom" name="pere_nom"
                 value="<?= $v('pere_nom') ?>" placeholder="Nom" style="text-transform:uppercase;">
        </div>
        <div class="form-group">
          <label class="form-label" for="pere_prenom">Prénom(s)</label>
          <input class="form-control" type="text" id="pere_prenom" name="pere_prenom"
                 value="<?= $v('pere_prenom') ?>" placeholder="Prénom(s)">
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label" for="pere_date_naissance">Date de naissance</label>
          <input class="form-control" type="date" id="pere_date_naissance" name="pere_date_naissance"
                 value="<?= $v('pere_date_naissance') ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="pere_lieu_naissance">Lieu de naissance</label>
          <input class="form-control" type="text" id="pere_lieu_naissance" name="pere_lieu_naissance"
                 value="<?= $v('pere_lieu_naissance') ?>" placeholder="Ville, pays">
        </div>
        <div class="form-group">
          <label class="form-label" for="pere_nationalite">Nationalité</label>
          <input class="form-control" type="text" id="pere_nationalite" name="pere_nationalite"
                 value="<?= $v('pere_nationalite') ?: 'Béninoise' ?>">
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="pere_profession">Profession</label>
          <input class="form-control" type="text" id="pere_profession" name="pere_profession"
                 value="<?= $v('pere_profession') ?>" placeholder="Profession">
        </div>
        <div class="form-group">
          <label class="form-label" for="pere_domicile">Domicile</label>
          <input class="form-control" type="text" id="pere_domicile" name="pere_domicile"
                 value="<?= $v('pere_domicile') ?>" placeholder="Adresse de domicile">
        </div>
      </div>

    </div>
  </div>

  <!-- SECTION : MÈRE -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Informations sur la mère</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="mere_nom">Nom de famille</label>
          <input class="form-control<?= $fClass('mere_nom') ?>" type="text" id="mere_nom" name="mere_nom"
                 value="<?= $v('mere_nom') ?>" required placeholder="Nom" style="text-transform:uppercase;">
          <?= $err('mere_nom') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="mere_prenom">Prénom(s)</label>
          <input class="form-control<?= $fClass('mere_prenom') ?>" type="text" id="mere_prenom" name="mere_prenom"
                 value="<?= $v('mere_prenom') ?>" required placeholder="Prénom(s)">
          <?= $err('mere_prenom') ?>
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label" for="mere_date_naissance">Date de naissance</label>
          <input class="form-control" type="date" id="mere_date_naissance" name="mere_date_naissance"
                 value="<?= $v('mere_date_naissance') ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="mere_lieu_naissance">Lieu de naissance</label>
          <input class="form-control" type="text" id="mere_lieu_naissance" name="mere_lieu_naissance"
                 value="<?= $v('mere_lieu_naissance') ?>" placeholder="Ville, pays">
        </div>
        <div class="form-group">
          <label class="form-label" for="mere_nationalite">Nationalité</label>
          <input class="form-control" type="text" id="mere_nationalite" name="mere_nationalite"
                 value="<?= $v('mere_nationalite') ?: 'Béninoise' ?>">
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="mere_profession">Profession</label>
          <input class="form-control" type="text" id="mere_profession" name="mere_profession"
                 value="<?= $v('mere_profession') ?>" placeholder="Profession">
        </div>
        <div class="form-group">
          <label class="form-label" for="mere_domicile">Domicile</label>
          <input class="form-control" type="text" id="mere_domicile" name="mere_domicile"
                 value="<?= $v('mere_domicile') ?>" placeholder="Adresse de domicile">
        </div>
      </div>

    </div>
  </div>

  <!-- SECTION : DÉCLARANT -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Déclarant</div>
    <div class="form-row">

      <div class="form-group">
        <label class="form-label form-label-required" for="declarant_qualite">Qualité du déclarant</label>
        <select class="form-control<?= $fClass('declarant_qualite') ?>" id="declarant_qualite" name="declarant_qualite" required>
          <option value="">Sélectionner</option>
          <?php foreach (['Père', 'Mère', 'Médecin', 'Sage-femme', 'Chef de famille', 'Chef de village', 'Voisin', 'Autre'] as $q): ?>
          <option value="<?= $q ?>" <?= $v('declarant_qualite') === $q ? 'selected' : '' ?>><?= $q ?></option>
          <?php endforeach; ?>
        </select>
        <?= $err('declarant_qualite') ?>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="declarant_nom">Nom du déclarant</label>
          <input class="form-control" type="text" id="declarant_nom" name="declarant_nom"
                 value="<?= $v('declarant_nom') ?>" placeholder="Nom" style="text-transform:uppercase;">
        </div>
        <div class="form-group">
          <label class="form-label" for="declarant_prenom">Prénom du déclarant</label>
          <input class="form-control" type="text" id="declarant_prenom" name="declarant_prenom"
                 value="<?= $v('declarant_prenom') ?>" placeholder="Prénom">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="declarant_domicile">Domicile du déclarant</label>
        <input class="form-control" type="text" id="declarant_domicile" name="declarant_domicile"
               value="<?= $v('declarant_domicile') ?>" placeholder="Adresse de domicile">
      </div>

    </div>
  </div>

  <!-- SECTION : OBSERVATIONS -->
  <div class="card mb-6">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Mentions & Observations</div>
    <div class="form-group">
      <label class="form-label" for="observations">Observations / Mentions marginales</label>
      <textarea class="form-control" id="observations" name="observations" rows="3"
                placeholder="Mentions légales, apostilles, rectifications antérieures..."><?= $v('observations') ?></textarea>
      <div class="form-hint">Ces mentions apparaîtront sur l'acte officiel.</div>
    </div>
  </div>

  <!-- ACTIONS -->
  <div style="display:flex;gap:var(--space-5);align-items:center;padding-bottom:var(--space-10);">
    <button type="submit" class="btn btn-primary btn-lg">
      <?= $isEdit ? 'Enregistrer les modifications' : 'Enregistrer l\'acte' ?>
    </button>
    <a href="/naissances" class="btn btn-ghost">Annuler</a>
    <?php if ($isEdit): ?>
    <span style="font-family:var(--font-mono);font-size:0.6875rem;color:var(--color-text-tertiary);margin-left:auto;">
      La modification sera tracée dans le journal d'audit
    </span>
    <?php endif; ?>
  </div>

</form>
