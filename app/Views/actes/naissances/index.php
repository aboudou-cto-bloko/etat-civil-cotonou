<?php
/** @var array       $resultats */
/** @var array       $filters */
/** @var array|null  $flash */
?>
<div class="page-header">
  <div class="page-header-row">
    <div>
      <div style="font-family:var(--font-mono);font-size:0.625rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:6px;">
        Actes de naissance
      </div>
      <h1 class="page-title">Naissances</h1>
      <p class="page-subtitle"><?= number_format($resultats['total']) ?> acte<?= $resultats['total'] > 1 ? 's' : '' ?> enregistré<?= $resultats['total'] > 1 ? 's' : '' ?></p>
    </div>
    <div class="page-actions">
      <a href="/naissances/nouveau" class="btn btn-primary">+ Nouveau acte</a>
    </div>
  </div>
</div>

<!-- FILTRES -->
<form method="GET" action="/naissances">
  <div class="filter-bar">
    <div class="form-group">
      <label class="form-label">Nom / Prénom</label>
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
      <a href="/naissances" class="btn btn-ghost">Réinitialiser</a>
    </div>
  </div>
</form>

<!-- TABLE -->
<?php if (empty($resultats['data'])): ?>
<div class="card">
  <div class="empty-state">
    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="var(--color-text-tertiary)" stroke-width="1.5">
      <circle cx="20" cy="20" r="18"/><line x1="20" y1="12" x2="20" y2="20"/><circle cx="20" cy="27" r="1" fill="currentColor"/>
    </svg>
    <div class="empty-state-title">Aucun acte trouvé</div>
    <div class="empty-state-sub">Modifiez vos filtres ou enregistrez un premier acte de naissance.</div>
    <a href="/naissances/nouveau" class="btn btn-primary">+ Nouveau acte</a>
  </div>
</div>
<?php else: ?>
<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>N° Acte / Année</th>
        <th>Nom de l'enfant</th>
        <th>Sexe</th>
        <th>Date de naissance</th>
        <th>Arrondissement</th>
        <th>Statut</th>
        <th>Enregistré le</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($resultats['data'] as $acte): ?>
      <tr>
        <td>
          <span class="acte-number"><?= \App\Core\View::e($acte['numero_acte']) ?>/<?= $acte['annee'] ?></span>
        </td>
        <td>
          <div class="td-primary"><?= \App\Core\View::e($acte['enfant_nom'] . ' ' . $acte['enfant_prenom']) ?></div>
        </td>
        <td>
          <?php if ($acte['enfant_sexe'] === 'M'): ?>
          <span class="badge badge-blue">Masculin</span>
          <?php else: ?>
          <span class="badge badge-red">Féminin</span>
          <?php endif; ?>
        </td>
        <td class="td-mono"><?= date('d/m/Y', strtotime($acte['date_naissance'])) ?></td>
        <td>
          <span style="font-family:var(--font-mono);font-size:0.75rem;color:var(--color-text-tertiary);">
            <?= \App\Core\View::e($acte['arrondissement_nom'] ?? ('Arr. ' . $acte['arrondissement_numero'])) ?>
          </span>
        </td>
        <td>
          <?php
          $statusMap = ['ACTIF' => 'badge-green', 'ANNULÉ' => 'badge-red', 'RECTIFIÉ' => 'badge-orange'];
          $statusClass = $statusMap[$acte['statut']] ?? 'badge-neutral';
          ?>
          <span class="badge <?= $statusClass ?>"><?= \App\Core\View::e($acte['statut']) ?></span>
        </td>
        <td class="td-mono"><?= date('d/m/Y', strtotime($acte['created_at'])) ?></td>
        <td class="td-actions">
          <div style="display:flex;gap:6px;justify-content:flex-end;">
            <a href="/naissances/<?= \App\Core\View::e($acte['id']) ?>" class="btn btn-ghost btn-sm">Voir</a>
            <a href="/actes/naissance/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-ghost btn-sm">PDF</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- PAGINATION -->
<?php if ($resultats['last_page'] > 1): ?>
<div class="pagination">
  <div class="pagination-info">
    Page <?= $resultats['current_page'] ?> sur <?= $resultats['last_page'] ?>
    &mdash; <?= number_format($resultats['total']) ?> résultats
  </div>
  <div class="pagination-pages">
    <?php
    $currentPage = $resultats['current_page'];
    $lastPage    = $resultats['last_page'];
    $query       = array_filter($filters);
    ?>
    <a href="?<?= http_build_query(array_merge($query, ['page' => max(1, $currentPage - 1)])) ?>"
       class="page-link <?= $currentPage <= 1 ? 'disabled' : '' ?>">
      &larr;
    </a>
    <?php for ($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++): ?>
    <a href="?<?= http_build_query(array_merge($query, ['page' => $p])) ?>"
       class="page-link <?= $p === $currentPage ? 'active' : '' ?>">
      <?= $p ?>
    </a>
    <?php endfor; ?>
    <a href="?<?= http_build_query(array_merge($query, ['page' => min($lastPage, $currentPage + 1)])) ?>"
       class="page-link <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
      &rarr;
    </a>
  </div>
</div>
<?php endif; ?>

<?php endif; ?>
