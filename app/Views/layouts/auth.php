<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= \App\Core\View::e($title ?? 'État Civil Cotonou') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="auth-body">

  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-dot">EC</div>
        <div class="auth-brand-title">État Civil</div>
        <div class="auth-brand-sub">Mairie de Cotonou &mdash; Bénin</div>
      </div>

      <?= $content ?>
    </div>

    <p style="text-align:center; margin-top: 24px; font-family: var(--font-mono); font-size: 0.625rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-tertiary);">
      &copy; <?= date('Y') ?> Mairie de Cotonou — Accès réservé aux agents habilités
    </p>
  </div>

</body>
</html>
