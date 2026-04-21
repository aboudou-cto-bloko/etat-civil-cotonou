<?php
$moisLabels = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$isAdmin    = ($_SESSION['user']['role_code'] ?? '') === 'admin';

$totalAll = ($stats_naissances['total'] ?? 0) + ($stats_mariages['total'] ?? 0) + ($stats_deces['total'] ?? 0);

$maxArrondissement = 1;
if (!empty($stats_arrondissements)) {
    foreach ($stats_arrondissements as $arr) {
        $t = ($arr['naissances'] + $arr['mariages'] + $arr['deces']);
        if ($t > $maxArrondissement) $maxArrondissement = $t;
    }
}
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <div style="font-family:var(--font-mono);font-size:0.625rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:6px;">
        Statistiques
      </div>
      <h1 class="page-title">Analyse des données</h1>
      <p class="page-subtitle">
        <?= $isAdmin ? 'Tous arrondissements' : \App\Core\View::e($_SESSION['user']['arrondissement_nom'] ?? '') ?>
        &mdash; Année <?= $annee ?>
      </p>
    </div>
    <div class="page-actions">
      <a href="/statistiques/export?annee=<?= $annee ?>" class="btn btn-secondary">
        Exporter CSV
      </a>
    </div>
  </div>
</div>

<!-- FILTRE PÉRIODE -->
<form method="GET" action="/statistiques" style="margin-bottom:var(--space-7);">
  <div class="filter-bar">
    <div class="form-group form-group--sm">
      <label class="form-label">Année</label>
      <input class="form-control" type="number" name="annee" value="<?= $annee ?>" min="1969" max="<?= date('Y') ?>">
    </div>
    <div class="form-group form-group--sm">
      <label class="form-label">Du</label>
      <input class="form-control" type="date" name="date_debut" value="<?= \App\Core\View::e($date_debut) ?>">
    </div>
    <div class="form-group form-group--sm">
      <label class="form-label">Au</label>
      <input class="form-control" type="date" name="date_fin" value="<?= \App\Core\View::e($date_fin) ?>">
    </div>
    <div class="filter-bar-actions">
      <button class="btn btn-primary" type="submit">Appliquer</button>
    </div>
  </div>
</form>

<!-- KPI TOTAUX -->
<div class="grid-4 mb-6">
  <div class="stat-card">
    <div class="stat-label">Total actes <?= $annee ?></div>
    <div class="stat-value"><?= number_format($totalAll) ?></div>
  </div>
  <div class="stat-card stat-card--naissances">
    <div class="stat-label">Naissances</div>
    <div class="stat-value"><?= number_format((int)($stats_naissances['total'] ?? 0)) ?></div>
    <div class="stat-sub">
      <div class="stat-sub-item">M <span><?= (int)($stats_naissances['masculin'] ?? 0) ?></span></div>
      <div class="stat-sub-item">F <span><?= (int)($stats_naissances['feminin'] ?? 0) ?></span></div>
    </div>
  </div>
  <div class="stat-card stat-card--mariages">
    <div class="stat-label">Mariages</div>
    <div class="stat-value"><?= number_format((int)($stats_mariages['total'] ?? 0)) ?></div>
    <div class="stat-sub">
      <div class="stat-sub-item">Mono <span><?= (int)($stats_mariages['monogamiques'] ?? 0) ?></span></div>
      <div class="stat-sub-item">Poly <span><?= (int)($stats_mariages['polygamiques'] ?? 0) ?></span></div>
    </div>
  </div>
  <div class="stat-card stat-card--deces">
    <div class="stat-label">Décès</div>
    <div class="stat-value"><?= number_format((int)($stats_deces['total'] ?? 0)) ?></div>
    <div class="stat-sub">
      <div class="stat-sub-item">M <span><?= (int)($stats_deces['masculin'] ?? 0) ?></span></div>
      <div class="stat-sub-item">F <span><?= (int)($stats_deces['feminin'] ?? 0) ?></span></div>
    </div>
  </div>
</div>

<div class="grid-2 mb-6">

  <!-- TABLEAU MENSUEL -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Répartition mensuelle</span>
      <span class="badge badge-neutral"><?= $annee ?></span>
    </div>
    <div class="table-container" style="border:none;border-radius:0;">
      <table>
        <thead>
          <tr>
            <th>Mois</th>
            <th style="color:var(--color-blue)">Naissances</th>
            <th style="color:var(--color-green)">Mariages</th>
            <th style="color:var(--color-red)">Décès</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $totNais = $totMar = $totDec = 0;
          foreach ($stats_par_mois as $mois => $stats):
            $tot = $stats['naissances'] + $stats['mariages'] + $stats['deces'];
            $totNais += $stats['naissances'];
            $totMar  += $stats['mariages'];
            $totDec  += $stats['deces'];
          ?>
          <tr>
            <td class="td-mono"><?= $moisLabels[$mois] ?></td>
            <td><?= $stats['naissances'] > 0 ? '<span style="color:#4d8ef5;font-weight:500;">' . $stats['naissances'] . '</span>' : '<span style="color:var(--color-text-tertiary)">—</span>' ?></td>
            <td><?= $stats['mariages']   > 0 ? '<span style="color:#4ee83b;font-weight:500;">' . $stats['mariages']   . '</span>' : '<span style="color:var(--color-text-tertiary)">—</span>' ?></td>
            <td><?= $stats['deces']      > 0 ? '<span style="color:#f57d73;font-weight:500;">' . $stats['deces']      . '</span>' : '<span style="color:var(--color-text-tertiary)">—</span>' ?></td>
            <td class="td-primary"><?= $tot > 0 ? $tot : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr style="border-top:var(--border-medium);">
            <td class="td-mono" style="color:var(--color-text-primary);font-weight:600;">Total</td>
            <td style="color:#4d8ef5;font-weight:600;"><?= $totNais ?></td>
            <td style="color:#4ee83b;font-weight:600;"><?= $totMar ?></td>
            <td style="color:#f57d73;font-weight:600;"><?= $totDec ?></td>
            <td class="td-primary" style="font-weight:600;"><?= $totNais + $totMar + $totDec ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- ACTIVITÉ PAR UTILISATEUR -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Activité par agent</span>
      <span class="badge badge-neutral"><?= $date_debut ?> → <?= $date_fin ?></span>
    </div>

    <?php if (empty($stats_by_user)): ?>
    <div class="empty-state" style="padding:var(--space-9) 0;">
      <span class="empty-state-sub">Aucune activité sur cette période.</span>
    </div>
    <?php else: ?>
    <div class="table-container" style="border:none;border-radius:0;">
      <table>
        <thead>
          <tr>
            <th>Agent</th>
            <th>Enreg.</th>
            <th>Modif.</th>
            <th>PDF</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($stats_by_user as $u): ?>
          <tr>
            <td>
              <div class="td-primary"><?= \App\Core\View::e(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')) ?></div>
              <div class="td-mono"><?= \App\Core\View::e($u['role_code'] ?? '') ?></div>
            </td>
            <td><?= (int)$u['creations'] ?></td>
            <td><?= (int)$u['modifications'] ?></td>
            <td><?= (int)$u['pdf_generes'] ?></td>
            <td class="td-primary"><?= (int)$u['total_actions'] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- PAR ARRONDISSEMENT (admin seulement) -->
<?php if ($isAdmin && !empty($stats_arrondissements)): ?>
<div class="card">
  <div class="card-header">
    <span class="card-title">Comparatif par arrondissement &mdash; <?= $annee ?></span>
    <span class="badge badge-neutral">13 arrondissements</span>
  </div>
  <div class="table-container" style="border:none;border-radius:0;">
    <table>
      <thead>
        <tr>
          <th>Arrondissement</th>
          <th style="color:var(--color-blue)">Naissances</th>
          <th style="color:var(--color-green)">Mariages</th>
          <th style="color:var(--color-red)">Décès</th>
          <th>Total</th>
          <th style="width:200px;">Proportion</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($stats_arrondissements as $arr):
          $tot = $arr['naissances'] + $arr['mariages'] + $arr['deces'];
          $pct = $maxArrondissement > 0 ? round(($tot / $maxArrondissement) * 100) : 0;
        ?>
        <tr>
          <td class="td-primary"><?= \App\Core\View::e($arr['nom']) ?></td>
          <td><?= (int)$arr['naissances'] ?></td>
          <td><?= (int)$arr['mariages'] ?></td>
          <td><?= (int)$arr['deces'] ?></td>
          <td class="td-primary"><?= $tot ?></td>
          <td>
            <div class="stats-bar">
              <div class="stats-bar-track">
                <div class="stats-bar-fill" style="width:<?= $pct ?>%;"></div>
              </div>
              <span class="td-mono"><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
