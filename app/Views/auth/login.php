<?php
/** @var string|null $error */
/** @var string|null $old_email */
?>
<?php if (!empty($error)): ?>
<div hidden data-flash="error" data-flash-message="<?= \App\Core\View::e($error) ?>"></div>
<?php endif; ?>

<form method="POST" action="/login" class="auth-form">
  <?= \App\Core\View::csrfField() ?>

  <div class="form-group">
    <label class="form-label" for="email">Adresse email</label>
    <input
      class="form-control"
      type="email"
      id="email"
      name="email"
      value="<?= \App\Core\View::e($old_email ?? '') ?>"
      required
      autocomplete="email"
      placeholder="agent@mairie-cotonou.bj"
      autofocus
    >
  </div>

  <div class="form-group">
    <label class="form-label" for="password">Mot de passe</label>
    <input
      class="form-control"
      type="password"
      id="password"
      name="password"
      required
      autocomplete="current-password"
      placeholder="••••••••"
    >
  </div>

  <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top: var(--space-3);">
    Se connecter
  </button>
</form>

<p style="margin-top: var(--space-8); text-align:center; font-family: var(--font-mono); font-size: 0.625rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-tertiary);">
  Accès sécurisé &mdash; Agents habilités uniquement
</p>
