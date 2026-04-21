<?php
/** @var array  $stats_naissances */
/** @var array  $stats_mariages */
/** @var array  $stats_deces */
/** @var array  $evolution_mensuelle */
/** @var int    $annee */
$moisLabels = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

// Calcul max pour normaliser les barres du graphique
$maxVal = 1;
foreach ($evolution_mensuelle as $m) {
    $maxVal = max($maxVal, $m['naissances'], $m['mariages'], $m['deces']);
}
?>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <div style="font-family:var(--font-mono);font-size:0.625rem;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:6px;">
        Tableau de bord — <?= $annee ?>
      </div>
      <h1 class="page-title">Vue d'ensemble</h1>
      <?php if (!empty($_SESSION['user']['arrondissement_nom'])): ?>
      <p class="page-subtitle"><?= \App\Core\View::e($_SESSION['user']['arrondissement_nom']) ?></p>
      <?php else: ?>
      <p class="page-subtitle">Tous arrondissements — Mairie de Cotonou</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- KPI CARDS -->
<div class="grid-3 mb-6">

  <!-- Naissances -->
  <div class="stat-card stat-card--naissances">
    <div class="stat-label">Naissances <?= $annee ?></div>
    <div class="stat-value"><?= number_format((int)($stats_naissances['total'] ?? 0)) ?></div>
    <div class="stat-sub">
      <div class="stat-sub-item">M <span><?= (int)($stats_naissances['masculin'] ?? 0) ?></span></div>
      <div class="stat-sub-item">F <span><?= (int)($stats_naissances['feminin'] ?? 0) ?></span></div>
      <?php if (($stats_naissances['jumeaux'] ?? 0) > 0): ?>
      <div class="stat-sub-item">Jumeaux <span><?= (int)$stats_naissances['jumeaux'] ?></span></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Mariages -->
  <div class="stat-card stat-card--mariages">
    <div class="stat-label">Mariages <?= $annee ?></div>
    <div class="stat-value"><?= number_format((int)($stats_mariages['total'] ?? 0)) ?></div>
    <div class="stat-sub">
      <div class="stat-sub-item">Mono <span><?= (int)($stats_mariages['monogamiques'] ?? 0) ?></span></div>
      <div class="stat-sub-item">Poly <span><?= (int)($stats_mariages['polygamiques'] ?? 0) ?></span></div>
    </div>
  </div>

  <!-- Décès -->
  <div class="stat-card stat-card--deces">
    <div class="stat-label">Décès <?= $annee ?></div>
    <div class="stat-value"><?= number_format((int)($stats_deces['total'] ?? 0)) ?></div>
    <div class="stat-sub">
      <div class="stat-sub-item">M <span><?= (int)($stats_deces['masculin'] ?? 0) ?></span></div>
      <div class="stat-sub-item">F <span><?= (int)($stats_deces['feminin'] ?? 0) ?></span></div>
    </div>
  </div>

</div>

<div class="grid-2" style="gap: var(--space-7);">

  <!-- GRAPHIQUE MENSUEL -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Évolution mensuelle <?= $annee ?></span>
      <span class="badge badge-neutral">Mois par mois</span>
    </div>
    <div class="chart-container" style="padding: 0 0 var(--space-7);">
      <div class="chart-bars">
        <?php foreach ($evolution_mensuelle as $mois => $stats): ?>
        <div class="chart-col">
          <div class="chart-bar-group">
            <div class="chart-bar chart-bar--naissances"
                 title="Naissances : <?= $stats['naissances'] ?>"
                 style="height: <?= $maxVal > 0 ? round(($stats['naissances'] / $maxVal) * 110) : 2 ?>px;">
            </div>
            <div class="chart-bar chart-bar--mariages"
                 title="Mariages : <?= $stats['mariages'] ?>"
                 style="height: <?= $maxVal > 0 ? round(($stats['mariages'] / $maxVal) * 110) : 2 ?>px;">
            </div>
            <div class="chart-bar chart-bar--deces"
                 title="Décès : <?= $stats['deces'] ?>"
                 style="height: <?= $maxVal > 0 ? round(($stats['deces'] / $maxVal) * 110) : 2 ?>px;">
            </div>
          </div>
          <div class="chart-label"><?= $moisLabels[$mois] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="chart-legend">
        <div class="chart-legend-item">
          <div class="chart-legend-dot" style="background:var(--color-green)"></div> Naissances
        </div>
        <div class="chart-legend-item">
          <div class="chart-legend-dot" style="background:var(--color-gold)"></div> Mariages
        </div>
        <div class="chart-legend-item">
          <div class="chart-legend-dot" style="background:var(--color-red)"></div> Décès
        </div>
      </div>
    </div>
  </div>

  <!-- ACTIVITÉ RÉCENTE -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Activité récente</span>
      <span class="badge badge-neutral"><?= count($activite_recente) ?> entrées</span>
    </div>

    <?php if (empty($activite_recente)): ?>
    <div class="empty-state" style="padding: var(--space-9) 0;">
      <span class="empty-state-sub">Aucune activité enregistrée.</span>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:0;">
      <?php foreach ($activite_recente as $log): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:var(--border-subtle);">
        <div style="display:flex;flex-direction:column;gap:2px;">
          <span style="font-size:0.8125rem;color:var(--color-text-primary);letter-spacing:-0.01em;">
            <?= \App\Core\View::e(($log['user_prenom'] ?? '') . ' ' . ($log['user_nom'] ?? '—')) ?>
          </span>
          <span style="font-family:var(--font-mono);font-size:0.625rem;color:var(--color-text-tertiary);text-transform:uppercase;letter-spacing:0.06em;">
            <?= \App\Core\View::e($log['action'] ?? '') ?>
            <?php if ($log['type_entite']): ?>
              &middot; <?= \App\Core\View::e($log['type_entite']) ?>
            <?php endif; ?>
          </span>
        </div>
        <span style="font-family:var(--font-mono);font-size:0.6875rem;color:var(--color-text-tertiary);white-space:nowrap;margin-left:16px;">
          <?= date('d/m H:i', strtotime($log['created_at'])) ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- ACCÈS RAPIDES (admin only) -->
<?php if (($_SESSION['user']['role_code'] ?? '') === 'admin'): ?>
<div class="card mt-6">
  <div class="card-header">
    <span class="card-title">Accès rapides</span>
  </div>
  <div class="grid-4" style="padding-top: var(--space-5);">
    <a href="/naissances/nouveau" class="btn btn-secondary" style="justify-content:flex-start;">
      + Nouvelle naissance
    </a>
    <a href="/mariages/nouveau" class="btn btn-secondary" style="justify-content:flex-start;">
      + Nouveau mariage
    </a>
    <a href="/deces/nouveau" class="btn btn-secondary" style="justify-content:flex-start;">
      + Nouveau décès
    </a>
    <a href="/statistiques" class="btn btn-secondary" style="justify-content:flex-start;">
      Statistiques &rarr;
    </a>
  </div>
</div>
<?php endif; ?>
