<?php
/** @var array  $utilisateur */
/** @var array  $errors */
/** @var array  $roles */
/** @var array  $arrondissements */
$isEdit   = !empty($utilisateur['id']);
$v        = fn($field) => \App\Core\View::e($utilisateur[$field] ?? '');
$err      = fn($field) => !empty($errors[$field]) ? '<div class="form-error">' . \App\Core\View::e($errors[$field]) . '</div>' : '';
$fClass   = fn($field) => !empty($errors[$field]) ? ' form-control--error' : '';
?>

<div class="breadcrumb">
  <a href="/utilisateurs">Utilisateurs</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-current"><?= $isEdit ? 'Modifier' : 'Nouveau compte' ?></span>
</div>

<div class="page-header">
  <h1 class="page-title"><?= $isEdit ? 'Modifier le compte' : 'Nouveau compte' ?></h1>
  <p class="page-subtitle">Renseigner le rôle et l'arrondissement attribués à cet agent.</p>
</div>

<form method="POST" action="<?= $isEdit ? '/utilisateurs/' . $v('id') . '/modifier' : '/utilisateurs' ?>">
  <?= \App\Core\View::csrfField() ?>

  <!-- IDENTITÉ -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Identité</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="nom">Nom de famille</label>
          <input class="form-control<?= $fClass('nom') ?>" type="text" id="nom" name="nom"
                 value="<?= $v('nom') ?>" required placeholder="NOM" style="text-transform:uppercase;">
          <?= $err('nom') ?>
        </div>
        <div class="form-group">
          <label class="form-label form-label-required" for="prenom">Prénom(s)</label>
          <input class="form-control<?= $fClass('prenom') ?>" type="text" id="prenom" name="prenom"
                 value="<?= $v('prenom') ?>" required placeholder="Prénom">
          <?= $err('prenom') ?>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="email">Adresse email</label>
          <input class="form-control<?= $fClass('email') ?>" type="email" id="email" name="email"
                 value="<?= $v('email') ?>" required placeholder="email@exemple.bj">
          <?= $err('email') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="telephone">Téléphone</label>
          <input class="form-control" type="text" id="telephone" name="telephone"
                 value="<?= $v('telephone') ?>" placeholder="+229 97 00 00 00">
        </div>
      </div>

      <div class="form-group" style="max-width:320px;">
        <label class="form-label" for="matricule">Matricule</label>
        <input class="form-control" type="text" id="matricule" name="matricule"
               value="<?= $v('matricule') ?>" placeholder="Ex : MC-2025-001">
        <div class="form-hint">Identifiant interne optionnel (doit être unique).</div>
      </div>

    </div>
  </div>

  <!-- ACCÈS -->
  <div class="card mb-5">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">Accès & périmètre</div>
    <div class="form-row">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label form-label-required" for="role_id">Rôle</label>
          <select class="form-control<?= $fClass('role_id') ?>" id="role_id" name="role_id" required>
            <option value="">Sélectionner un rôle</option>
            <?php foreach ($roles as $role): ?>
            <option value="<?= $role['id'] ?>"
              <?= ($utilisateur['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
              <?= \App\Core\View::e($role['libelle']) ?> (<?= \App\Core\View::e($role['code']) ?>)
            </option>
            <?php endforeach; ?>
          </select>
          <?= $err('role_id') ?>
          <div class="form-hint">
            <strong>admin</strong> — accès global &bull;
            <strong>superviseur</strong> — un arrondissement &bull;
            <strong>analytics</strong> — statistiques uniquement
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="arrondissement_id">Arrondissement</label>
          <select class="form-control" id="arrondissement_id" name="arrondissement_id">
            <option value="">Mairie centrale (accès global)</option>
            <?php foreach ($arrondissements as $arr): ?>
            <option value="<?= $arr['id'] ?>"
              <?= ($utilisateur['arrondissement_id'] ?? '') == $arr['id'] ? 'selected' : '' ?>>
              <?= \App\Core\View::e($arr['nom']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div class="form-hint">Obligatoire pour les rôles superviseur et analytics.</div>
        </div>
      </div>

    </div>
  </div>

  <!-- MOT DE PASSE -->
  <div class="card mb-6">
    <div class="form-section-title" style="margin-bottom:var(--space-7);">
      <?= $isEdit ? 'Nouveau mot de passe' : 'Mot de passe' ?>
    </div>
    <?php if ($isEdit): ?>
    <p style="font-size:0.875rem;color:var(--color-text-secondary);margin-bottom:var(--space-5);">
      Laisser vide pour conserver le mot de passe actuel.
    </p>
    <?php endif; ?>
    <div class="form-row">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label <?= $isEdit ? '' : 'form-label-required' ?>" for="password">
            Mot de passe<?= $isEdit ? ' (optionnel)' : '' ?>
          </label>
          <input class="form-control<?= $fClass('password') ?>" type="password" id="password" name="password"
                 <?= $isEdit ? '' : 'required' ?> placeholder="8 caractères minimum" autocomplete="new-password">
          <?= $err('password') ?>
        </div>
        <div class="form-group">
          <label class="form-label <?= $isEdit ? '' : 'form-label-required' ?>" for="password_confirm">Confirmer</label>
          <input class="form-control<?= $fClass('password_confirm') ?>" type="password" id="password_confirm"
                 name="password_confirm" <?= $isEdit ? '' : 'required' ?> placeholder="Répéter le mot de passe" autocomplete="new-password">
          <?= $err('password_confirm') ?>
        </div>
      </div>
    </div>
  </div>

  <div style="display:flex;gap:var(--space-5);align-items:center;padding-bottom:var(--space-10);">
    <button type="submit" class="btn btn-primary btn-lg">
      <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le compte' ?>
    </button>
    <a href="/utilisateurs" class="btn btn-ghost">Annuler</a>
    <?php if ($isEdit): ?>
    <span style="font-family:var(--font-mono);font-size:0.6875rem;color:var(--color-text-tertiary);margin-left:auto;">
      La modification sera tracée dans le journal d'audit
    </span>
    <?php endif; ?>
  </div>

</form>
