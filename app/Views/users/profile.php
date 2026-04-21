<?php
$u      = $utilisateur ?? $_SESSION['user'] ?? [];
$err    = fn($field) => !empty($errors[$field]) ? '<div class="form-error">' . \App\Core\View::e($errors[$field]) . '</div>' : '';
$fClass = fn($field) => !empty($errors[$field]) ? ' form-control--error' : '';
$roleColors = ['admin' => 'badge-red', 'superviseur' => 'badge-blue', 'analytics' => 'badge-neutral'];
?>

<div class="page-header">
  <h1 class="page-title">Mon profil</h1>
  <p class="page-subtitle">Informations de votre compte et modification du mot de passe.</p>
</div>

<?php if (!empty($flash)): ?>
<div style="margin-bottom:var(--space-5);padding:var(--space-4) var(--space-5);border-radius:var(--radius-md);background:<?= $flash['type'] === 'success' ? 'rgba(34,197,94,0.1)' : 'rgba(243,100,88,0.1)' ?>;border:1px solid <?= $flash['type'] === 'success' ? 'rgba(34,197,94,0.3)' : 'rgba(243,100,88,0.3)' ?>;color:<?= $flash['type'] === 'success' ? '#4ade80' : 'var(--color-red)' ?>;">
  <?= \App\Core\View::e($flash['message']) ?>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);align-items:start;">

  <!-- INFOS COMPTE -->
  <div class="card">
    <div class="form-section-title" style="margin-bottom:var(--space-6);">Informations du compte</div>
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
    <div class="form-section-title" style="margin-bottom:var(--space-6);">Modifier mes informations</div>
    <form method="POST" action="/profil">
      <?= \App\Core\View::csrfField() ?>

      <div class="form-group" style="margin-bottom:var(--space-5);">
        <label class="form-label" for="telephone">Téléphone</label>
        <input class="form-control<?= $fClass('telephone') ?>" type="text" id="telephone" name="telephone"
               value="<?= \App\Core\View::e($u['telephone'] ?? '') ?>" placeholder="+229 97 00 00 00">
        <?= $err('telephone') ?>
      </div>

      <div style="border-top:1px solid var(--color-border);padding-top:var(--space-5);margin-top:var(--space-5);">
        <div class="form-section-title" style="margin-bottom:var(--space-5);font-size:0.75rem;">Changer le mot de passe</div>
        <p style="font-size:0.8125rem;color:var(--color-text-secondary);margin-bottom:var(--space-4);">
          Laisser vide pour ne pas modifier le mot de passe.
        </p>

        <div class="form-group" style="margin-bottom:var(--space-4);">
          <label class="form-label" for="current_password">Mot de passe actuel</label>
          <input class="form-control<?= $fClass('current_password') ?>" type="password" id="current_password"
                 name="current_password" placeholder="••••••••" autocomplete="current-password">
          <?= $err('current_password') ?>
        </div>
        <div class="form-group" style="margin-bottom:var(--space-4);">
          <label class="form-label" for="new_password">Nouveau mot de passe</label>
          <input class="form-control<?= $fClass('new_password') ?>" type="password" id="new_password"
                 name="new_password" placeholder="8 caractères minimum" autocomplete="new-password">
          <?= $err('new_password') ?>
        </div>
        <div class="form-group" style="margin-bottom:var(--space-6);">
          <label class="form-label" for="new_password_confirm">Confirmer le nouveau mot de passe</label>
          <input class="form-control<?= $fClass('new_password_confirm') ?>" type="password" id="new_password_confirm"
                 name="new_password_confirm" placeholder="Répéter" autocomplete="new-password">
          <?= $err('new_password_confirm') ?>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
  </div>

</div>
