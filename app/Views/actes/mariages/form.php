<?php
$isEdit = !empty($acte['id']);
$v      = fn($field) => \App\Core\View::e($acte[$field] ?? '');
$err    = fn($field) => !empty($errors[$field]) ? '<div class="form-error">' . \App\Core\View::e($errors[$field]) . '</div>' : '';
$fClass = fn($field) => !empty($errors[$field]) ? ' form-control--error' : '';
$user   = $_SESSION['user'] ?? [];
$statutOptions = ['CÉLIBATAIRE', 'DIVORCÉ(E)', 'VEUF/VEUVE'];
?>

<div class="breadcrumb">
  <a href="/mariages">Mariages</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-current"><?= $isEdit ? 'Modifier' : 'Nouveau acte' ?></span>
</div>

<div class="page-header">
  <h1 class="page-title"><?= $isEdit ? 'Modifier l\'acte de mariage' : 'Nouveau acte de mariage' ?></h1>
  <p class="page-subtitle">Saisir les informations conformément à l'Ordonnance n°69-23 et à la Loi n°2002-07 (CPF Bénin).</p>
</div>

<form method="POST" action="<?= $isEdit ? '/mariages/' . $v('id') . '/modifier' : '/mariages' ?>">
  <?= \App\Core\View::csrfField() ?>

  <!-- REGISTRE -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Registre & cérémonie</div>
    <div class="form-row">

      <div class="form-grid-3">
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
          <label class="form-label form-label-required" for="date_mariage">Date du mariage</label>
          <input class="form-control<?= $fClass('date_mariage') ?>" type="date" id="date_mariage" name="date_mariage"
                 value="<?= $v('date_mariage') ?>" required>
          <?= $err('date_mariage') ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="heure_mariage">Heure de la cérémonie</label>
          <input class="form-control" type="time" id="heure_mariage" name="heure_mariage" value="<?= $v('heure_mariage') ?>">
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="lieu_celebration">Lieu de célébration</label>
          <input class="form-control<?= $fClass('lieu_celebration') ?>" type="text" id="lieu_celebration" name="lieu_celebration"
                 value="<?= $v('lieu_celebration') ?>" required placeholder="Ex: Mairie de l'arrondissement 6">
          <?= $err('lieu_celebration') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="regime_matrimonial">Régime matrimonial</label>
          <select class="form-control" id="regime_matrimonial" name="regime_matrimonial">
            <option value="">Non précisé</option>
            <?php foreach (['Communauté réduite aux acquêts', 'Séparation de biens', 'Régime dotal'] as $r): ?>
            <option value="<?= $r ?>" <?= $v('regime_matrimonial') === $r ? 'selected' : '' ?>><?= $r ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label form-label-required">Type de mariage</label>
        <div style="display:flex;gap:var(--space-7);margin-top:4px;">
          <label class="form-check">
            <input type="radio" name="type_mariage" value="MONOGAMIQUE"
                   <?= ($v('type_mariage') ?: 'MONOGAMIQUE') === 'MONOGAMIQUE' ? 'checked' : '' ?>>
            <span class="form-check-label">Monogamique</span>
          </label>
          <label class="form-check">
            <input type="radio" name="type_mariage" value="POLYGAMIQUE"
                   <?= $v('type_mariage') === 'POLYGAMIQUE' ? 'checked' : '' ?>>
            <span class="form-check-label">Polygamique</span>
          </label>
        </div>
      </div>

    </div>
  </div>

  <!-- ÉPOUX -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Époux</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="epoux_nom">Nom de famille</label>
          <input class="form-control<?= $fClass('epoux_nom') ?>" type="text" id="epoux_nom" name="epoux_nom"
                 value="<?= $v('epoux_nom') ?>" required placeholder="Nom" style="text-transform:uppercase;">
          <?= $err('epoux_nom') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="epoux_prenom">Prénom(s)</label>
          <input class="form-control<?= $fClass('epoux_prenom') ?>" type="text" id="epoux_prenom" name="epoux_prenom"
                 value="<?= $v('epoux_prenom') ?>" required placeholder="Prénom(s)">
          <?= $err('epoux_prenom') ?>
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label form-label-required" for="epoux_date_naissance">Date de naissance</label>
          <input class="form-control<?= $fClass('epoux_date_naissance') ?>" type="date" id="epoux_date_naissance"
                 name="epoux_date_naissance" value="<?= $v('epoux_date_naissance') ?>" required>
          <?= $err('epoux_date_naissance') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="epoux_lieu_naissance">Lieu de naissance</label>
          <input class="form-control" type="text" id="epoux_lieu_naissance" name="epoux_lieu_naissance"
                 value="<?= $v('epoux_lieu_naissance') ?>" placeholder="Ville, pays">
        </div>
        <div class="form-group">
          <label class="form-label" for="epoux_nationalite">Nationalité</label>
          <input class="form-control" type="text" id="epoux_nationalite" name="epoux_nationalite"
                 value="<?= $v('epoux_nationalite') ?: 'Béninoise' ?>">
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="epoux_profession">Profession</label>
          <input class="form-control" type="text" id="epoux_profession" name="epoux_profession"
                 value="<?= $v('epoux_profession') ?>" placeholder="Profession">
        </div>
        <div class="form-group">
          <label class="form-label" for="epoux_domicile">Domicile</label>
          <input class="form-control" type="text" id="epoux_domicile" name="epoux_domicile"
                 value="<?= $v('epoux_domicile') ?>" placeholder="Adresse de domicile">
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label" for="epoux_statut_anterieur">Statut antérieur</label>
          <select class="form-control" id="epoux_statut_anterieur" name="epoux_statut_anterieur">
            <?php foreach ($statutOptions as $s): ?>
            <option value="<?= $s ?>" <?= $v('epoux_statut_anterieur') === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="epoux_pere_nom_prenom">Père (nom & prénom)</label>
          <input class="form-control" type="text" id="epoux_pere_nom_prenom" name="epoux_pere_nom_prenom"
                 value="<?= $v('epoux_pere_nom_prenom') ?>" placeholder="Nom et prénom du père">
        </div>
        <div class="form-group">
          <label class="form-label" for="epoux_mere_nom_prenom">Mère (nom & prénom)</label>
          <input class="form-control" type="text" id="epoux_mere_nom_prenom" name="epoux_mere_nom_prenom"
                 value="<?= $v('epoux_mere_nom_prenom') ?>" placeholder="Nom et prénom de la mère">
        </div>
      </div>

    </div>
  </div>

  <!-- ÉPOUSE -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Épouse principale</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="epouse_nom">Nom de famille</label>
          <input class="form-control<?= $fClass('epouse_nom') ?>" type="text" id="epouse_nom" name="epouse_nom"
                 value="<?= $v('epouse_nom') ?>" required placeholder="Nom" style="text-transform:uppercase;">
          <?= $err('epouse_nom') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="epouse_prenom">Prénom(s)</label>
          <input class="form-control<?= $fClass('epouse_prenom') ?>" type="text" id="epouse_prenom" name="epouse_prenom"
                 value="<?= $v('epouse_prenom') ?>" required placeholder="Prénom(s)">
          <?= $err('epouse_prenom') ?>
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label form-label-required" for="epouse_date_naissance">Date de naissance</label>
          <input class="form-control<?= $fClass('epouse_date_naissance') ?>" type="date" id="epouse_date_naissance"
                 name="epouse_date_naissance" value="<?= $v('epouse_date_naissance') ?>" required>
          <?= $err('epouse_date_naissance') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="epouse_lieu_naissance">Lieu de naissance</label>
          <input class="form-control" type="text" id="epouse_lieu_naissance" name="epouse_lieu_naissance"
                 value="<?= $v('epouse_lieu_naissance') ?>" placeholder="Ville, pays">
        </div>
        <div class="form-group">
          <label class="form-label" for="epouse_nationalite">Nationalité</label>
          <input class="form-control" type="text" id="epouse_nationalite" name="epouse_nationalite"
                 value="<?= $v('epouse_nationalite') ?: 'Béninoise' ?>">
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="epouse_profession">Profession</label>
          <input class="form-control" type="text" id="epouse_profession" name="epouse_profession"
                 value="<?= $v('epouse_profession') ?>" placeholder="Profession">
        </div>
        <div class="form-group">
          <label class="form-label" for="epouse_domicile">Domicile</label>
          <input class="form-control" type="text" id="epouse_domicile" name="epouse_domicile"
                 value="<?= $v('epouse_domicile') ?>" placeholder="Adresse de domicile">
        </div>
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label" for="epouse_statut_anterieur">Statut antérieur</label>
          <select class="form-control" id="epouse_statut_anterieur" name="epouse_statut_anterieur">
            <?php foreach ($statutOptions as $s): ?>
            <option value="<?= $s ?>" <?= $v('epouse_statut_anterieur') === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="epouse_pere_nom_prenom">Père (nom & prénom)</label>
          <input class="form-control" type="text" id="epouse_pere_nom_prenom" name="epouse_pere_nom_prenom"
                 value="<?= $v('epouse_pere_nom_prenom') ?>" placeholder="Nom et prénom du père">
        </div>
        <div class="form-group">
          <label class="form-label" for="epouse_mere_nom_prenom">Mère (nom & prénom)</label>
          <input class="form-control" type="text" id="epouse_mere_nom_prenom" name="epouse_mere_nom_prenom"
                 value="<?= $v('epouse_mere_nom_prenom') ?>" placeholder="Nom et prénom de la mère">
        </div>
      </div>

    </div>
  </div>

  <!-- BANS & CONSENTEMENTS -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Publication des bans & Consentements</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label" for="date_publication_bans">Date de publication des bans</label>
          <input class="form-control" type="date" id="date_publication_bans" name="date_publication_bans"
                 value="<?= $v('date_publication_bans') ?>">
          <div class="form-hint">Publication légale obligatoire 10 jours avant le mariage.</div>
        </div>
        <div class="form-group">
          <label class="form-label" for="lieu_publication_bans">Lieu de publication</label>
          <input class="form-control" type="text" id="lieu_publication_bans" name="lieu_publication_bans"
                 value="<?= $v('lieu_publication_bans') ?>" placeholder="Ex: Mairie Arr. 6">
        </div>
      </div>

      <div style="display:flex;flex-wrap:wrap;gap:var(--space-8);">
        <div class="form-check">
          <input type="checkbox" id="opposition_recue" name="opposition_recue" value="1"
                 <?= ($acte['opposition_recue'] ?? 0) ? 'checked' : '' ?>>
          <label class="form-check-label" for="opposition_recue">Opposition reçue</label>
        </div>
        <div class="form-check">
          <input type="checkbox" id="consentement_parents_epoux" name="consentement_parents_epoux" value="1"
                 <?= ($acte['consentement_parents_epoux'] ?? 0) ? 'checked' : '' ?>>
          <label class="form-check-label" for="consentement_parents_epoux">Consentement des parents de l'époux</label>
        </div>
        <div class="form-check">
          <input type="checkbox" id="consentement_parents_epouse" name="consentement_parents_epouse" value="1"
                 <?= ($acte['consentement_parents_epouse'] ?? 0) ? 'checked' : '' ?>>
          <label class="form-check-label" for="consentement_parents_epouse">Consentement des parents de l'épouse</label>
        </div>
        <div class="form-check">
          <input type="checkbox" id="autorisation_judiciaire" name="autorisation_judiciaire" value="1"
                 <?= ($acte['autorisation_judiciaire'] ?? 0) ? 'checked' : '' ?>>
          <label class="form-check-label" for="autorisation_judiciaire">Autorisation judiciaire obtenue</label>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="detail_opposition">Détail de l'opposition (si applicable)</label>
        <textarea class="form-control" id="detail_opposition" name="detail_opposition" rows="2"
                  placeholder="Motif de l'opposition reçue..."><?= $v('detail_opposition') ?></textarea>
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
    <a href="/mariages" class="btn btn-ghost">Annuler</a>
    <?php if ($isEdit): ?>
    <span style="font-family:var(--font-mono);font-size:0.6875rem;color:var(--color-text-tertiary);margin-left:auto;">
      La modification sera tracée dans le journal d'audit
    </span>
    <?php endif; ?>
  </div>

</form>
