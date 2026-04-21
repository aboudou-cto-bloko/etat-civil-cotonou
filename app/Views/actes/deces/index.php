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
        Actes de décès
      </div>
      <h1 class="page-title">Décès</h1>
      <p class="page-subtitle"><?= number_format($resultats['total']) ?> acte<?= $resultats['total'] > 1 ? 's' : '' ?> enregistré<?= $resultats['total'] > 1 ? 's' : '' ?></p>
    </div>
    <div class="page-actions">
      <a href="/deces/nouveau" class="btn btn-primary">+ Nouveau acte</a>
    </div>
  </div>
</div>

<!-- FILTRES -->
<form method="GET" action="/deces">
  <input type="hidden" name="sort" value="<?= \App\Core\View::e($sort) ?>">
  <input type="hidden" name="direction" value="<?= \App\Core\View::e($direction) ?>">
  <div class="filter-bar">
    <div class="form-group">
      <label class="form-label">Nom / Prénom du défunt</label>
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
      <label class="form-label">Du</label>
      <input class="form-control" type="date" name="date_debut" value="<?= \App\Core\View::e($filters['date_debut']) ?>">
    </div>
    <div class="form-group form-group--sm">
      <label class="form-label">Au</label>
      <input class="form-control" type="date" name="date_fin" value="<?= \App\Core\View::e($filters['date_fin']) ?>">
    </div>
    <div class="filter-bar-actions">
      <button class="btn btn-primary" type="submit">Filtrer</button>
      <a href="/deces" class="btn btn-ghost">Réinitialiser</a>
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
      <a href="/deces" class="btn btn-ghost">Réinitialiser les filtres</a>
    <?php else: ?>
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="var(--color-text-tertiary)" stroke-width="1.5">
        <rect x="8" y="4" width="24" height="30" rx="2"/>
        <line x1="14" y1="13" x2="26" y2="13"/><line x1="14" y1="19" x2="22" y2="19"/>
        <line x1="14" y1="25" x2="19" y2="25"/>
      </svg>
      <div class="empty-state-title">Aucun acte de décès enregistré</div>
      <div class="empty-state-sub">Commencez par enregistrer le premier acte de décès.</div>
      <a href="/deces/nouveau" class="btn btn-primary">+ Enregistrer un acte</a>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<div class="table-container">
  <table>
    <thead>
      <tr>
        <th><a href="<?= $sortUrl('numero_acte') ?>" class="th-sort <?= $sort === 'numero_acte' ? 'th-sort--active' : '' ?>">N° Acte / Année<?= $sortIcon('numero_acte') ?></a></th>
        <th><a href="<?= $sortUrl('defunt_nom') ?>" class="th-sort <?= $sort === 'defunt_nom' ? 'th-sort--active' : '' ?>">Défunt<?= $sortIcon('defunt_nom') ?></a></th>
        <th>Sexe</th>
        <th><a href="<?= $sortUrl('date_deces') ?>" class="th-sort <?= $sort === 'date_deces' ? 'th-sort--active' : '' ?>">Date du décès<?= $sortIcon('date_deces') ?></a></th>
        <th>Lieu du décès</th>
        <th>Arrondissement</th>
        <th>Statut</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($resultats['data'] as $acte): ?>
      <tr>
        <td><span class="acte-number"><?= \App\Core\View::e($acte['numero_acte']) ?>/<?= $acte['annee'] ?></span></td>
        <td class="td-primary"><?= \App\Core\View::e($acte['defunt_nom'] . ' ' . $acte['defunt_prenom']) ?></td>
        <td>
          <?= $acte['defunt_sexe'] === 'M'
            ? '<span class="badge badge-blue">M</span>'
            : '<span class="badge badge-red">F</span>' ?>
        </td>
        <td class="td-mono"><?= date('d/m/Y', strtotime($acte['date_deces'])) ?></td>
        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--color-text-secondary);">
          <?= \App\Core\View::e($acte['lieu_deces']) ?>
        </td>
        <td class="td-mono"><?= \App\Core\View::e($acte['arrondissement_nom'] ?? ('Arr. ' . $acte['arrondissement_numero'])) ?></td>
        <td>
          <?php $statusMap = ['ACTIF' => 'badge-green', 'ANNULÉ' => 'badge-red', 'RECTIFIÉ' => 'badge-orange']; ?>
          <span class="badge <?= $statusMap[$acte['statut']] ?? 'badge-neutral' ?>"><?= \App\Core\View::e($acte['statut']) ?></span>
        </td>
        <td class="td-actions">
          <div style="display:flex;gap:6px;justify-content:flex-end;">
            <a href="/deces/<?= \App\Core\View::e($acte['id']) ?>" class="btn btn-ghost btn-sm">Voir</a>
            <a href="/actes/deces/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-ghost btn-sm">PDF</a>
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
