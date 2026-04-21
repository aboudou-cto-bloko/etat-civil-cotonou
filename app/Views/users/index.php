<div class="page-header">
  <div class="page-header-row">
    <div>
      <div style="font-family:var(--font-mono);font-size:0.625rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:6px;">Administration</div>
      <h1 class="page-title">Utilisateurs</h1>
      <p class="page-subtitle"><?= count($users) ?> compte(s) enregistré(s)</p>
    </div>
    <div class="page-actions">
      <a href="/utilisateurs/nouveau" class="btn btn-primary">+ Nouveau compte</a>
    </div>
  </div>
</div>
<div class="table-container">
  <table>
    <thead><tr><th>Matricule</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Arrondissement</th><th>Statut</th><th>Dernière connexion</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td class="td-mono"><?= \App\Core\View::e($u['matricule'] ?? '—') ?></td>
        <td class="td-primary"><?= \App\Core\View::e($u['prenom'] . ' ' . $u['nom']) ?></td>
        <td class="td-mono"><?= \App\Core\View::e($u['email']) ?></td>
        <td><span class="badge badge-blue"><?= \App\Core\View::e($u['role_code']) ?></span></td>
        <td class="td-mono"><?= \App\Core\View::e($u['arrondissement_nom'] ?? 'Mairie centrale') ?></td>
        <td><?= $u['is_active'] ? '<span class="badge badge-green">Actif</span>' : '<span class="badge badge-red">Inactif</span>' ?></td>
        <td class="td-mono"><?= $u['last_login_at'] ? date('d/m/Y H:i', strtotime($u['last_login_at'])) : '—' ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
