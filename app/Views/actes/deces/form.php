<?php
/** @var array      $acte */
/** @var array      $errors */
/** @var array      $arrondissements */
$isEdit = !empty($acte['id']);
$v      = fn($field) => \App\Core\View::e($acte[$field] ?? '');
$err    = fn($field) => !empty($errors[$field]) ? '<div class="form-error">' . \App\Core\View::e($errors[$field]) . '</div>' : '';
$fClass = fn($field) => !empty($errors[$field]) ? ' form-control--error' : '';
$user   = $_SESSION['user'] ?? [];
?>

<div class="breadcrumb">
  <a href="/deces">Décès</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-current"><?= $isEdit ? 'Modifier' : 'Nouveau acte' ?></span>
</div>

<div class="page-header">
  <h1 class="page-title"><?= $isEdit ? 'Modifier l\'acte de décès' : 'Nouveau acte de décès' ?></h1>
  <p class="page-subtitle">Saisir les informations conformément à l'Ordonnance n°69-23 du 10 juillet 1969. Le décès doit être déclaré dans les 24h.</p>
</div>

<form method="POST" action="<?= $isEdit ? '/deces/' . $v('id') . '/modifier' : '/deces' ?>">
  <?= \App\Core\View::csrfField() ?>

  <!-- REGISTRE & CIRCONSTANCES -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Registre & circonstances du décès</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="arrondissement_id">Arrondissement</label>
          <?php if (!empty($user['arrondissement_id'])): ?>
            <input class="form-control" type="text" value="<?= \App\Core\View::e($user['arrondissement_nom'] ?? '') ?>" disabled>
            <input type="hidden" name="arrondissement_id" value="<?= \App\Core\View::e($user['arrondissement_id']) ?>">
          <?php else: ?>
            <select class="form-control<?= $fClass('arrondissement_id') ?>" name="arrondissement_id" required>
              <option value="">Sélectionner</option>
              <?php foreach ($arrondissements as $arr): ?>
              <option value="<?= $arr['id'] ?>" <?= ($acte['arrondissement_id'] ?? '') == $arr['id'] ? 'selected' : '' ?>>
                <?= \App\Core\View::e($arr['nom']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="date_declaration">Date de déclaration</label>
          <input class="form-control<?= $fClass('date_declaration') ?>" type="date" id="date_declaration" name="date_declaration"
                 value="<?= $v('date_declaration') ?: date('Y-m-d') ?>" required>
          <?= $err('date_declaration') ?>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="date_deces">Date et heure du décès</label>
          <input class="form-control<?= $fClass('date_deces') ?>" type="datetime-local" id="date_deces" name="date_deces"
                 value="<?= $v('date_deces') ?>" required>
          <?= $err('date_deces') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="lieu_deces">Lieu du décès</label>
          <input class="form-control<?= $fClass('lieu_deces') ?>" type="text" id="lieu_deces" name="lieu_deces"
                 value="<?= $v('lieu_deces') ?>" required placeholder="Ex: CHU de Cotonou, Akpakpa">
          <?= $err('lieu_deces') ?>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="cause_deces">Cause du décès</label>
          <input class="form-control" type="text" id="cause_deces" name="cause_deces"
                 value="<?= $v('cause_deces') ?>" placeholder="Facultatif — selon certificat médical">
          <div class="form-hint">Mentionnée uniquement si un certificat médical est fourni.</div>
        </div>
        <div class="form-group">
          <label class="form-label" for="numero_certificat_medical">N° certificat médical</label>
          <div style="display:flex;gap:var(--space-6);align-items:flex-start;flex-direction:column;">
            <div class="form-check">
              <input type="checkbox" id="certificat_medical_fourni" name="certificat_medical_fourni" value="1"
                     <?= ($acte['certificat_medical_fourni'] ?? 0) ? 'checked' : '' ?>>
              <label class="form-check-label" for="certificat_medical_fourni">Certificat médical fourni</label>
            </div>
            <input class="form-control" type="text" id="numero_certificat_medical" name="numero_certificat_medical"
                   value="<?= $v('numero_certificat_medical') ?>" placeholder="Référence du certificat">
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- DÉFUNT -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Identité du défunt</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="defunt_nom">Nom de famille</label>
          <input class="form-control<?= $fClass('defunt_nom') ?>" type="text" id="defunt_nom" name="defunt_nom"
                 value="<?= $v('defunt_nom') ?>" required placeholder="Nom" style="text-transform:uppercase;">
          <?= $err('defunt_nom') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="defunt_prenom">Prénom(s)</label>
          <input class="form-control<?= $fClass('defunt_prenom') ?>" type="text" id="defunt_prenom" name="defunt_prenom"
                 value="<?= $v('defunt_prenom') ?>" required placeholder="Prénom(s)">
          <?= $err('defunt_prenom') ?>
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label form-label-required" for="defunt_sexe">Sexe</label>
          <select class="form-control<?= $fClass('defunt_sexe') ?>" id="defunt_sexe" name="defunt_sexe" required>
            <option value="">—</option>
            <option value="M" <?= $v('defunt_sexe') === 'M' ? 'selected' : '' ?>>Masculin</option>
            <option value="F" <?= $v('defunt_sexe') === 'F' ? 'selected' : '' ?>>Féminin</option>
          </select>
          <?= $err('defunt_sexe') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="defunt_date_naissance">Date de naissance</label>
          <input class="form-control" type="date" id="defunt_date_naissance" name="defunt_date_naissance"
                 value="<?= $v('defunt_date_naissance') ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="defunt_lieu_naissance">Lieu de naissance</label>
          <input class="form-control" type="text" id="defunt_lieu_naissance" name="defunt_lieu_naissance"
                 value="<?= $v('defunt_lieu_naissance') ?>" placeholder="Ville, pays">
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label" for="defunt_nationalite">Nationalité</label>
          <input class="form-control" type="text" id="defunt_nationalite" name="defunt_nationalite"
                 value="<?= $v('defunt_nationalite') ?: 'Béninoise' ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="defunt_profession">Profession</label>
          <input class="form-control" type="text" id="defunt_profession" name="defunt_profession"
                 value="<?= $v('defunt_profession') ?>" placeholder="Profession">
        </div>
        <div class="form-group">
          <label class="form-label" for="defunt_situation_matrimoniale">Situation matrimoniale</label>
          <select class="form-control" id="defunt_situation_matrimoniale" name="defunt_situation_matrimoniale">
            <option value="">Non précisée</option>
            <?php foreach (['CÉLIBATAIRE', 'MARIÉ(E)', 'VEUF/VEUVE', 'DIVORCÉ(E)'] as $s): ?>
            <option value="<?= $s ?>" <?= $v('defunt_situation_matrimoniale') === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="defunt_domicile">Domicile du défunt</label>
        <input class="form-control" type="text" id="defunt_domicile" name="defunt_domicile"
               value="<?= $v('defunt_domicile') ?>" placeholder="Adresse de domicile">
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="defunt_pere_nom_prenom">Père (nom & prénom)</label>
          <input class="form-control" type="text" id="defunt_pere_nom_prenom" name="defunt_pere_nom_prenom"
                 value="<?= $v('defunt_pere_nom_prenom') ?>" placeholder="Filiation paternelle">
        </div>
        <div class="form-group">
          <label class="form-label" for="defunt_mere_nom_prenom">Mère (nom & prénom)</label>
          <input class="form-control" type="text" id="defunt_mere_nom_prenom" name="defunt_mere_nom_prenom"
                 value="<?= $v('defunt_mere_nom_prenom') ?>" placeholder="Filiation maternelle">
        </div>
      </div>

    </div>
  </div>

  <!-- DÉCLARANT -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Déclarant</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="declarant_nom">Nom du déclarant</label>
          <input class="form-control<?= $fClass('declarant_nom') ?>" type="text" id="declarant_nom" name="declarant_nom"
                 value="<?= $v('declarant_nom') ?>" required placeholder="Nom" style="text-transform:uppercase;">
          <?= $err('declarant_nom') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="declarant_prenom">Prénom du déclarant</label>
          <input class="form-control" type="text" id="declarant_prenom" name="declarant_prenom"
                 value="<?= $v('declarant_prenom') ?>" placeholder="Prénom">
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="declarant_qualite">Qualité du déclarant</label>
          <select class="form-control<?= $fClass('declarant_qualite') ?>" id="declarant_qualite" name="declarant_qualite" required>
            <option value="">Sélectionner</option>
            <?php foreach (['Conjoint(e)', 'Enfant', 'Parent', 'Médecin', 'Chef de famille', 'Chef de quartier', 'Autorité administrative', 'Autre'] as $q): ?>
            <option value="<?= $q ?>" <?= $v('declarant_qualite') === $q ? 'selected' : '' ?>><?= $q ?></option>
            <?php endforeach; ?>
          </select>
          <?= $err('declarant_qualite') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="declarant_domicile">Domicile du déclarant</label>
          <input class="form-control" type="text" id="declarant_domicile" name="declarant_domicile"
                 value="<?= $v('declarant_domicile') ?>" placeholder="Adresse de domicile">
        </div>
      </div>

    </div>
  </div>

  <!-- OBSERVATIONS -->
  <div class="card mb-6">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Observations</div>
    <div class="form-group">
      <label class="form-label" for="observations">Mentions marginales / Observations</label>
      <textarea class="form-control" id="observations" name="observations" rows="3"
                placeholder="Mentions légales, rectifications, annotations..."><?= $v('observations') ?></textarea>
    </div>
  </div>

  <div style="display:flex;gap:var(--space-5);align-items:center;padding-bottom:var(--space-10);">
    <button type="submit" class="btn btn-primary btn-lg">
      <?= $isEdit ? 'Enregistrer les modifications' : 'Enregistrer l\'acte' ?>
    </button>
    <a href="/deces" class="btn btn-ghost">Annuler</a>
    <?php if ($isEdit): ?>
    <span style="font-family:var(--font-mono);font-size:0.6875rem;color:var(--color-text-tertiary);margin-left:auto;">
      La modification sera tracée dans le journal d'audit
    </span>
    <?php endif; ?>
  </div>

</form>
