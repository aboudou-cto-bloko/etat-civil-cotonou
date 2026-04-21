<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= \App\Core\View::e($title ?? 'État Civil') ?> — Mairie de Cotonou</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<?php
  $u          = $_SESSION['user'] ?? [];
  $role       = $u['role_code'] ?? '';
  $path       = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  $isAdmin    = $role === 'admin';
  $canEdit    = in_array($role, ['admin', 'superviseur']);
  $navActive  = static fn(string $prefix, string $current): string =>
                  str_starts_with($current, $prefix) ? ' active' : '';
?>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="navbar-brand">
    <div style="width:28px;height:28px;background:var(--color-red);border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:600;color:#fff;flex-shrink:0;">EC</div>
    <div>
      <div class="navbar-brand-text">État Civil &mdash; Cotonou</div>
    </div>
  </div>

  <div class="navbar-right">
    <?php if ($u['arrondissement_nom'] ?? null): ?>
      <span class="navbar-badge"><?= \App\Core\View::e($u['arrondissement_nom']) ?></span>
    <?php else: ?>
      <span class="navbar-badge navbar-badge--admin">Mairie centrale</span>
    <?php endif; ?>

    <span class="navbar-user-name"><?= \App\Core\View::e(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></span>
    <a href="/logout" class="btn btn-ghost btn-sm">Déconnexion</a>
  </div>
</nav>

<!-- LAYOUT -->
<div class="layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-section-label">Navigation</div>
    <a href="/dashboard" class="nav-item<?= $navActive('/dashboard', $path) ?>">
      <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/>
        <rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/>
      </svg>
      Tableau de bord
    </a>

    <?php if ($canEdit): ?>
    <div class="sidebar-section-label">Actes</div>
    <a href="/naissances" class="nav-item<?= $navActive('/naissances', $path) ?>">
      <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="8" cy="6" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
      </svg>
      Naissances
    </a>
    <a href="/mariages" class="nav-item<?= $navActive('/mariages', $path) ?>">
      <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M5 7l3-4 3 4"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/>
      </svg>
      Mariages
    </a>
    <a href="/deces" class="nav-item<?= $navActive('/deces', $path) ?>">
      <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <rect x="2" y="1" width="12" height="14" rx="1"/>
        <line x1="5" y1="6" x2="11" y2="6"/><line x1="5" y1="9" x2="9" y2="9"/>
      </svg>
      Décès
    </a>
    <?php endif; ?>

    <div class="sidebar-section-label">Analyse</div>
    <a href="/statistiques" class="nav-item<?= $navActive('/statistiques', $path) ?>">
      <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M2 14V9M6 14V5M10 14V8M14 14V2"/>
      </svg>
      Statistiques
    </a>

    <?php if ($isAdmin): ?>
    <div class="sidebar-section-label">Administration</div>
    <a href="/utilisateurs" class="nav-item<?= $navActive('/utilisateurs', $path) ?>">
      <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="8" cy="5" r="3"/><path d="M1 14c0-3.9 3.1-7 7-7s7 3.1 7 7"/>
      </svg>
      Utilisateurs
    </a>
    <?php endif; ?>

    <div class="sidebar-section-label">Compte</div>
    <a href="/profil" class="nav-item<?= $navActive('/profil', $path) ?>">
      <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="8" cy="8" r="6"/><circle cx="8" cy="6" r="2"/><path d="M4 13c0-2.2 1.8-4 4-4s4 1.8 4 4"/>
      </svg>
      Mon profil
    </a>
  </aside>

  <!-- MAIN -->
  <main class="main-content">
    <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= \App\Core\View::e($flash['type']) ?>">
      <?= \App\Core\View::e($flash['message']) ?>
    </div>
    <?php endif; ?>

    <?= $content ?>
  </main>

</div>

<script src="/assets/js/app.js"></script>
</body>
</html>
