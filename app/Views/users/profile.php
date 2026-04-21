<?php $u = $_SESSION['user'] ?? []; ?>
<div class="page-header"><h1 class="page-title">Mon profil</h1></div>
<div class="card" style="max-width:480px;">
  <div class="detail-grid">
    <div class="detail-row"><div class="detail-key">Nom</div><div class="detail-value"><?= \App\Core\View::e(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></div></div>
    <div class="detail-row"><div class="detail-key">Email</div><div class="detail-value"><?= \App\Core\View::e($u['email'] ?? '') ?></div></div>
    <div class="detail-row"><div class="detail-key">Rôle</div><div class="detail-value"><span class="badge badge-blue"><?= \App\Core\View::e($u['role_code'] ?? '') ?></span></div></div>
    <div class="detail-row"><div class="detail-key">Arrondissement</div><div class="detail-value"><?= \App\Core\View::e($u['arrondissement_nom'] ?? 'Mairie centrale') ?></div></div>
  </div>
</div>
