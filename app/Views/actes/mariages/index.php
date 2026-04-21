<?php
/** @var array       $resultats */
/** @var array       $filters */
/** @var string      $sort */
/** @var string      $direction */
/** @var array|null  $flash */

$hasFilters = array_filter($filters);
$sortUrl = fn(string $col) => '?' . http_build_query(array_merge(
    array_filter($filters),
    ['sort' => $col, 'direction' => ($sort === $col && $direction === 'asc') ? 'desc' : 'asc']
));
$sortIcon = fn(string $col) => $sort === $col
    ? ($direction === 'asc' ? ' <span class="sort-icon sort-icon--asc">↑</span>' : ' <span class="sort-icon sort-icon--desc">↓</span>')
    : ' <span class="sort-icon sort-icon--idle">↕</span>';
?>
<div class="page-header">
  <div class="page-header-row">
    <div>
      <div style="font-family:var(--font-mono);font-size:0.625rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:6px;">
        Actes de mariage
      </div>
      <h1 class="page-title">Mariages</h1>
      <p class="page-subtitle"><?= number_format($resultats['total']) ?> acte<?= $resultats['total'] > 1 ? 's' : '' ?> enregistré<?= $resultats['total'] > 1 ? 's' : '' ?></p>
    </div>
    <div class="page-actions">
      <a href="/mariages/nouveau" class="btn btn-primary">+ Nouveau acte</a>
    </div>
  </div>
</div>

<!-- FILTRES -->
<form method="GET" action="/mariages">
  <input type="hidden" name="sort" value="<?= \App\Core\View::e($sort) ?>">
  <input type="hidden" name="direction" value="<?= \App\Core\View::e($direction) ?>">
  <div class="filter-bar">
    <div class="form-group">
      <label class="form-label">Nom époux / épouse</label>
      <input class="form-control" type="text" name="nom" value="<?= \App\Core\View::e($filters['nom']) ?>" placeholder="Rechercher...">
    </div>
    <div class="form-group form-group--sm">
      <label class="form-label">N° acte</label>
      <input class="form-control" type="text" name="numero_acte" value="<?= \App\Core\View::e($filters['numero_acte']) ?>" placeholder="0001">
    </div>
    <div class="form-group form-group--sm">
      <label class="form-label">Année</label>
      <input class="form-control" type="number" name="annee" value="<?= \App\Core\View::e($filters['annee']) ?>" placeholder="<?= date('Y') ?>" min="1969" max="<?= date('Y') ?>">
    </div>
    <div class="form-group form-group--sm">
      <label class="form-label">Type</label>
      <select class="form-control" name="type_mariage">
        <option value="">Tous</option>
        <option value="MONOGAMIQUE" <?= $filters['type_mariage'] === 'MONOGAMIQUE' ? 'selected' : '' ?>>Monogamique</option>
        <option value="POLYGAMIQUE" <?= $filters['type_mariage'] === 'POLYGAMIQUE' ? 'selected' : '' ?>>Polygamique</option>
      </select>
    </div>
    <div class="filter-bar-actions">
      <button class="btn btn-primary" type="submit">Filtrer</button>
      <a href="/mariages" class="btn btn-ghost">Réinitialiser</a>
    </div>
  </div>
</form>

<!-- TABLE -->
<?php if (empty($resultats['data'])): ?>
<div class="card">
  <div class="empty-state">
    <?php if ($hasFilters): ?>
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="var(--color-text-tertiary)" stroke-width="1.5">
        <circle cx="18" cy="18" r="12"/><line x1="27" y1="27" x2="36" y2="36"/>
        <line x1="13" y1="18" x2="23" y2="18"/><line x1="18" y1="13" x2="18" y2="23"/>
      </svg>
      <div class="empty-state-title">Aucun résultat pour ces filtres</div>
      <div class="empty-state-sub">Essayez d'élargir la recherche ou vérifiez l'orthographe.</div>
      <a href="/mariages" class="btn btn-ghost">Réinitialiser les filtres</a>
    <?php else: ?>
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="var(--color-text-tertiary)" stroke-width="1.5">
        <path d="M10 20 Q20 8 30 20"/><circle cx="10" cy="20" r="3"/><circle cx="30" cy="20" r="3"/>
        <line x1="20" y1="20" x2="20" y2="32"/><line x1="14" y1="32" x2="26" y2="32"/>
      </svg>
      <div class="empty-state-title">Aucun acte de mariage enregistré</div>
      <div class="empty-state-sub">Commencez par enregistrer le premier acte de mariage.</div>
      <a href="/mariages/nouveau" class="btn btn-primary">+ Enregistrer un acte</a>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<div class="table-container">
  <table>
    <thead>
      <tr>
        <th><a href="<?= $sortUrl('numero_acte') ?>" class="th-sort <?= $sort === 'numero_acte' ? 'th-sort--active' : '' ?>">N° Acte / Année<?= $sortIcon('numero_acte') ?></a></th>
        <th><a href="<?= $sortUrl('epoux_nom') ?>" class="th-sort <?= $sort === 'epoux_nom' ? 'th-sort--active' : '' ?>">Époux<?= $sortIcon('epoux_nom') ?></a></th>
        <th>Épouse</th>
        <th>Type</th>
        <th><a href="<?= $sortUrl('date_mariage') ?>" class="th-sort <?= $sort === 'date_mariage' ? 'th-sort--active' : '' ?>">Date du mariage<?= $sortIcon('date_mariage') ?></a></th>
        <th>Arrondissement</th>
        <th>Statut</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($resultats['data'] as $acte): ?>
      <tr>
        <td><span class="acte-number"><?= \App\Core\View::e($acte['numero_acte']) ?>/<?= $acte['annee'] ?></span></td>
        <td class="td-primary"><?= \App\Core\View::e($acte['epoux_nom'] . ' ' . $acte['epoux_prenom']) ?></td>
        <td class="td-primary"><?= \App\Core\View::e($acte['epouse_nom'] . ' ' . $acte['epouse_prenom']) ?></td>
        <td>
          <?php if ($acte['type_mariage'] === 'POLYGAMIQUE'): ?>
          <span class="badge badge-orange">Polygamique</span>
          <?php else: ?>
          <span class="badge badge-neutral">Monogamique</span>
          <?php endif; ?>
        </td>
        <td class="td-mono"><?= date('d/m/Y', strtotime($acte['date_mariage'])) ?></td>
        <td class="td-mono"><?= \App\Core\View::e($acte['arrondissement_nom'] ?? ('Arr. ' . $acte['arrondissement_numero'])) ?></td>
        <td>
          <?php $statusMap = ['ACTIF' => 'badge-green', 'ANNULÉ' => 'badge-red', 'DISSOUS' => 'badge-orange', 'RECTIFIÉ' => 'badge-orange']; ?>
          <span class="badge <?= $statusMap[$acte['statut']] ?? 'badge-neutral' ?>"><?= \App\Core\View::e($acte['statut']) ?></span>
        </td>
        <td class="td-actions">
          <div style="display:flex;gap:6px;justify-content:flex-end;">
            <a href="/mariages/<?= \App\Core\View::e($acte['id']) ?>" class="btn btn-ghost btn-sm">Voir</a>
            <a href="/actes/mariage/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-ghost btn-sm">PDF</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($resultats['last_page'] > 1): ?>
<div class="pagination">
  <div class="pagination-info">
    Page <?= $resultats['current_page'] ?> sur <?= $resultats['last_page'] ?> &mdash; <?= number_format($resultats['total']) ?> résultats
  </div>
  <div class="pagination-pages">
    <?php
    $currentPage = $resultats['current_page'];
    $lastPage    = $resultats['last_page'];
    $query       = array_filter(array_merge($filters, ['sort' => $sort, 'direction' => $direction]));
    ?>
    <a href="?<?= http_build_query(array_merge($query, ['page' => max(1, $currentPage - 1)])) ?>" class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>">&larr;</a>
    <?php for ($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++): ?>
    <a href="?<?= http_build_query(array_merge($query, ['page' => $p])) ?>" class="page-link <?= $p === $currentPage ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <a href="?<?= http_build_query(array_merge($query, ['page' => min($lastPage, $currentPage + 1)])) ?>" class="page-link <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">&rarr;</a>
  </div>
</div>
<?php endif; ?>

<?php endif; ?>
