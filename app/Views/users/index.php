<?php
/** @var array       $users */
/** @var array       $filters */
/** @var array       $roles */
/** @var array       $arrondissements */
/** @var array|null  $flash */
$currentUserId = $_SESSION['user']['id'] ?? '';
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <div style="font-family:var(--font-mono);font-size:0.625rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:6px;">Administration</div>
      <h1 class="page-title">Utilisateurs</h1>
      <p class="page-subtitle"><?= count($users) ?> compte<?= count($users) > 1 ? 's' : '' ?> enregistré<?= count($users) > 1 ? 's' : '' ?></p>
    </div>
    <div class="page-actions">
      <a href="/utilisateurs/nouveau" class="btn btn-primary">+ Nouveau compte</a>
    </div>
  </div>
</div>

<!-- FILTRES -->
<form method="GET" action="/utilisateurs">
  <div class="filter-bar">
    <div class="form-group form-group--sm">
      <label class="form-label">Rôle</label>
      <select class="form-control" name="role">
        <option value="">Tous les rôles</option>
        <?php foreach ($roles as $r): ?>
        <option value="<?= \App\Core\View::e($r['code']) ?>" <?= ($filters['role'] ?? '') === $r['code'] ? 'selected' : '' ?>>
          <?= \App\Core\View::e($r['libelle']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group form-group--sm">
      <label class="form-label">Arrondissement</label>
      <select class="form-control" name="arrondissement">
        <option value="">Tous</option>
        <?php foreach ($arrondissements as $arr): ?>
        <option value="<?= $arr['id'] ?>" <?= ($filters['arrondissement'] ?? '') == $arr['id'] ? 'selected' : '' ?>>
          <?= \App\Core\View::e($arr['nom']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group form-group--sm">
      <label class="form-label">Statut</label>
      <select class="form-control" name="statut">
        <option value="">Tous</option>
        <option value="1" <?= ($filters['statut'] ?? '') === '1' ? 'selected' : '' ?>>Actif</option>
        <option value="0" <?= ($filters['statut'] ?? '') === '0' ? 'selected' : '' ?>>Inactif</option>
      </select>
    </div>
    <div class="filter-bar-actions">
      <button class="btn btn-primary" type="submit">Filtrer</button>
      <a href="/utilisateurs" class="btn btn-ghost">Réinitialiser</a>
    </div>
  </div>
</form>

<?php if (empty($users)): ?>
<div class="card">
  <div class="empty-state">
    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="var(--color-text-tertiary)" stroke-width="1.5">
      <circle cx="20" cy="16" r="7"/><path d="M6 36c0-7.732 6.268-14 14-14s14 6.268 14 14"/>
    </svg>
    <div class="empty-state-title">Aucun compte trouvé</div>
    <div class="empty-state-sub">Modifiez vos filtres ou créez un premier compte.</div>
    <a href="/utilisateurs/nouveau" class="btn btn-primary">+ Nouveau compte</a>
  </div>
</div>
<?php else: ?>
<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>Matricule</th>
        <th>Nom & Prénom</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Arrondissement</th>
        <th>Statut</th>
        <th>Dernière connexion</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td class="td-mono"><?= \App\Core\View::e($u['matricule'] ?? '—') ?></td>
        <td class="td-primary"><?= \App\Core\View::e($u['prenom'] . ' ' . $u['nom']) ?></td>
        <td class="td-mono" style="font-size:0.8125rem;color:var(--color-text-secondary);"><?= \App\Core\View::e($u['email']) ?></td>
        <td>
          <?php $roleColors = ['admin' => 'badge-red', 'superviseur' => 'badge-blue', 'analytics' => 'badge-neutral']; ?>
          <span class="badge <?= $roleColors[$u['role_code']] ?? 'badge-neutral' ?>"><?= \App\Core\View::e($u['role_code']) ?></span>
        </td>
        <td class="td-mono" style="font-size:0.8125rem;">
          <?= $u['arrondissement_nom']
            ? \App\Core\View::e($u['arrondissement_nom'])
            : '<span style="color:var(--color-text-tertiary);">Mairie centrale</span>' ?>
        </td>
        <td>
          <?= $u['is_active']
            ? '<span class="badge badge-green">Actif</span>'
            : '<span class="badge badge-red">Inactif</span>' ?>
        </td>
        <td class="td-mono" style="font-size:0.8125rem;color:var(--color-text-secondary);">
          <?= $u['last_login_at'] ? date('d/m/Y H:i', strtotime($u['last_login_at'])) : '—' ?>
        </td>
        <td>
          <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
            <a href="/utilisateurs/<?= \App\Core\View::e($u['id']) ?>/modifier" class="btn btn-ghost btn-sm">Modifier</a>
            <?php if ($u['id'] !== $currentUserId): ?>
            <form method="POST" action="/utilisateurs/<?= \App\Core\View::e($u['id']) ?>/desactiver" style="margin:0;"
                  data-confirm="<?= $u['is_active'] ? 'Désactiver ce compte ?' : 'Réactiver ce compte ?' ?>"
                  data-confirm-body="<?= $u['is_active'] ? 'L\'agent ne pourra plus se connecter.' : 'L\'agent pourra de nouveau se connecter.' ?>"
                  data-confirm-label="<?= $u['is_active'] ? 'Désactiver' : 'Réactiver' ?>"
                  data-confirm-variant="<?= $u['is_active'] ? 'danger' : 'info' ?>">
              <?= \App\Core\View::csrfField() ?>
              <button type="submit" class="btn btn-ghost btn-sm"
                      style="color:<?= $u['is_active'] ? 'var(--color-red)' : '#4ade80' ?>;">
                <?= $u['is_active'] ? 'Désactiver' : 'Réactiver' ?>
              </button>
            </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
