<?php
/** @var array       $utilisateur */
/** @var array       $errors */
/** @var array|null  $flash */
$u      = $utilisateur ?? $_SESSION['user'] ?? [];
$err    = fn($field) => !empty($errors[$field]) ? '<div class="form-error">' . \App\Core\View::e($errors[$field]) . '</div>' : '';
$fClass = fn($field) => !empty($errors[$field]) ? ' form-control--error' : '';
$roleColors = ['admin' => 'badge-red', 'superviseur' => 'badge-blue', 'analytics' => 'badge-neutral'];
?>

<div class="page-header">
  <h1 class="page-title">Mon profil</h1>
  <p class="page-subtitle">Informations de votre compte et modification du mot de passe.</p>
</div>

<div class="grid-2" style="align-items:start;">

  <!-- INFOS COMPTE -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Informations du compte</div>
    </div>
    <div class="detail-grid">
      <div class="detail-row">
        <div class="detail-key">Nom & Prénom</div>
        <div class="detail-value"><?= \App\Core\View::e(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></div>
      </div>
      <div class="detail-row">
        <div class="detail-key">Email</div>
        <div class="detail-value" style="font-family:var(--font-mono);font-size:0.875rem;"><?= \App\Core\View::e($u['email'] ?? '') ?></div>
      </div>
      <div class="detail-row">
        <div class="detail-key">Matricule</div>
        <div class="detail-value" style="font-family:var(--font-mono);"><?= \App\Core\View::e($u['matricule'] ?? '—') ?></div>
      </div>
      <div class="detail-row">
        <div class="detail-key">Rôle</div>
        <div class="detail-value">
          <span class="badge <?= $roleColors[$u['role_code'] ?? ''] ?? 'badge-neutral' ?>">
            <?= \App\Core\View::e($u['role_code'] ?? '') ?>
          </span>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-key">Arrondissement</div>
        <div class="detail-value"><?= \App\Core\View::e($u['arrondissement_nom'] ?? 'Mairie centrale') ?></div>
      </div>
      <?php if (!empty($u['last_login_at'])): ?>
      <div class="detail-row">
        <div class="detail-key">Dernière connexion</div>
        <div class="detail-value" style="font-family:var(--font-mono);font-size:0.875rem;">
          <?= date('d/m/Y à H:i', strtotime($u['last_login_at'])) ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- FORMULAIRE MODIFICATION -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Modifier mes informations</div>
    </div>
    <form method="POST" action="/profil">
      <?= \App\Core\View::csrfField() ?>

      <div class="form-section">
        <div class="form-group">
          <label class="form-label" for="telephone">Téléphone</label>
          <input class="form-control<?= $fClass('telephone') ?>" type="text" id="telephone" name="telephone"
                 value="<?= \App\Core\View::e($u['telephone'] ?? '') ?>" placeholder="+229 97 00 00 00">
          <?= $err('telephone') ?>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-title">Changer le mot de passe</div>
        <p class="form-hint" style="margin-bottom:var(--space-8);">Laisser vide pour ne pas modifier le mot de passe.</p>

        <div class="form-group">
          <label class="form-label" for="current_password">Mot de passe actuel</label>
          <input class="form-control<?= $fClass('current_password') ?>" type="password" id="current_password"
                 name="current_password" placeholder="••••••••" autocomplete="current-password">
          <?= $err('current_password') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="new_password">Nouveau mot de passe</label>
          <input class="form-control<?= $fClass('new_password') ?>" type="password" id="new_password"
                 name="new_password" placeholder="8 caractères minimum" autocomplete="new-password">
          <?= $err('new_password') ?>
        </div>
        <div class="form-group">
          <label class="form-label" for="new_password_confirm">Confirmer le nouveau mot de passe</label>
          <input class="form-control<?= $fClass('new_password_confirm') ?>" type="password" id="new_password_confirm"
                 name="new_password_confirm" placeholder="Répéter" autocomplete="new-password">
          <?= $err('new_password_confirm') ?>
        </div>
      </div>

      <div style="padding-top:var(--space-8);">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>

</div>
