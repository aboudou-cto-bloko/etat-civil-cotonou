<?php
/** @var array      $acte */
/** @var array      $temoins */
$statusMap   = ['ACTIF' => 'badge-green', 'ANNULÉ' => 'badge-red', 'RECTIFIÉ' => 'badge-orange'];
$statusClass = $statusMap[$acte['statut'] ?? 'ACTIF'] ?? 'badge-neutral';
$canEdit     = in_array($_SESSION['user']['role_code'] ?? '', ['admin', 'superviseur']);
$empty       = fn($v) => !empty($v) ? \App\Core\View::e($v) : '<span class="detail-value--empty">Non renseigné</span>';
?>

<div class="breadcrumb">
  <a href="/naissances">Naissances</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-current">Acte n°<?= \App\Core\View::e($acte['numero_acte']) ?>/<?= $acte['annee'] ?></span>
</div>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
        <span class="acte-number">N° <?= \App\Core\View::e($acte['numero_acte']) ?>/<?= $acte['annee'] ?></span>
        <span class="badge <?= $statusClass ?>"><?= \App\Core\View::e($acte['statut']) ?></span>
      </div>
      <h1 class="page-title">
        <?= \App\Core\View::e($acte['enfant_nom'] . ' ' . $acte['enfant_prenom']) ?>
      </h1>
      <p class="page-subtitle">
        Acte de naissance &mdash; <?= \App\Core\View::e($acte['arrondissement_nom'] ?? '') ?>
      </p>
    </div>
    <?php if ($canEdit && $acte['statut'] === 'ACTIF'): ?>
    <div class="page-actions">
      <a href="/actes/naissance/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-secondary">
        Générer PDF
      </a>
      <a href="/naissances/<?= \App\Core\View::e($acte['id']) ?>/modifier" class="btn btn-primary">
        Modifier
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ENFANT -->
<div class="card mb-5">
  <div class="detail-grid">
    <div class="detail-section-title">Informations sur l'enfant</div>

    <div class="detail-row">
      <div class="detail-key">Nom</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['enfant_nom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Prénom(s)</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['enfant_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Sexe</div>
      <div class="detail-value">
        <?= $acte['enfant_sexe'] === 'M'
          ? '<span class="badge badge-blue">Masculin</span>'
          : '<span class="badge badge-red">Féminin</span>' ?>
      </div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date et heure de naissance</div>
      <div class="detail-value"><?= date('d/m/Y à H:i', strtotime($acte['date_naissance'])) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Commune de naissance</div>
      <div class="detail-value"><?= $empty($acte['lieu_naissance_commune']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Localité / Quartier</div>
      <div class="detail-value"><?= $empty($acte['lieu_naissance_localite']) ?></div>
    </div>
    <?php if ($acte['enfant_est_jumeau']): ?>
    <div class="detail-row">
      <div class="detail-key">Naissance multiple</div>
      <div class="detail-value">Oui &mdash; <?= $acte['ordre_jumeau'] ?>er né</div>
    </div>
    <?php endif; ?>

    <div class="detail-section-title">Père</div>

    <div class="detail-row">
      <div class="detail-key">Statut</div>
      <div class="detail-value"><?= $empty($acte['pere_statut']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Nom & Prénom</div>
      <div class="detail-value">
        <?= $acte['pere_nom']
          ? \App\Core\View::e($acte['pere_nom'] . ' ' . $acte['pere_prenom'])
          : '<span class="detail-value--empty">Non renseigné</span>' ?>
      </div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date de naissance</div>
      <div class="detail-value"><?= $acte['pere_date_naissance'] ? date('d/m/Y', strtotime($acte['pere_date_naissance'])) : '<span class="detail-value--empty">—</span>' ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Nationalité</div>
      <div class="detail-value"><?= $empty($acte['pere_nationalite']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Profession</div>
      <div class="detail-value"><?= $empty($acte['pere_profession']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Domicile</div>
      <div class="detail-value"><?= $empty($acte['pere_domicile']) ?></div>
    </div>

    <div class="detail-section-title">Mère</div>

    <div class="detail-row">
      <div class="detail-key">Nom & Prénom</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['mere_nom'] . ' ' . $acte['mere_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date de naissance</div>
      <div class="detail-value"><?= $acte['mere_date_naissance'] ? date('d/m/Y', strtotime($acte['mere_date_naissance'])) : '<span class="detail-value--empty">—</span>' ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Nationalité</div>
      <div class="detail-value"><?= $empty($acte['mere_nationalite']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Profession</div>
      <div class="detail-value"><?= $empty($acte['mere_profession']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Domicile</div>
      <div class="detail-value"><?= $empty($acte['mere_domicile']) ?></div>
    </div>

    <div class="detail-section-title">Déclarant</div>

    <div class="detail-row">
      <div class="detail-key">Qualité</div>
      <div class="detail-value"><?= $empty($acte['declarant_qualite']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Nom & Prénom</div>
      <div class="detail-value">
        <?= ($acte['declarant_nom'] ?? '') ? \App\Core\View::e($acte['declarant_nom'] . ' ' . $acte['declarant_prenom']) : '<span class="detail-value--empty">—</span>' ?>
      </div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Domicile</div>
      <div class="detail-value"><?= $empty($acte['declarant_domicile'] ?? '') ?></div>
    </div>

    <?php if (!empty($temoins)): ?>
    <div class="detail-section-title">Témoins</div>
    <?php foreach ($temoins as $temoin): ?>
    <div class="detail-row">
      <div class="detail-key">Témoin n°<?= $temoin['ordre'] ?></div>
      <div class="detail-value">
        <?= \App\Core\View::e($temoin['nom'] . ' ' . $temoin['prenom']) ?>
        <?php if ($temoin['profession']): ?>
        <span style="color:var(--color-text-tertiary);font-size:0.875em;">, <?= \App\Core\View::e($temoin['profession']) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="detail-section-title">Métadonnées du registre</div>

    <div class="detail-row">
      <div class="detail-key">Arrondissement</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['arrondissement_nom'] ?? '') ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date de déclaration</div>
      <div class="detail-value"><?= date('d/m/Y', strtotime($acte['date_declaration'])) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Enregistré par</div>
      <div class="detail-value"><?= \App\Core\View::e(($acte['enregistre_par_prenom'] ?? '') . ' ' . ($acte['enregistre_par_nom'] ?? '')) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date d'enregistrement</div>
      <div class="detail-value"><?= date('d/m/Y à H:i', strtotime($acte['created_at'])) ?></div>
    </div>
    <?php if (!empty($acte['observations'])): ?>
    <div class="detail-row" style="grid-column: 1 / -1;">
      <div class="detail-key">Observations / Mentions marginales</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['observations']) ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>

<div style="display:flex;gap:var(--space-5);">
  <a href="/naissances" class="btn btn-ghost">&larr; Retour à la liste</a>
  <?php if ($canEdit && $acte['statut'] === 'ACTIF'): ?>
  <a href="/naissances/<?= \App\Core\View::e($acte['id']) ?>/modifier" class="btn btn-secondary">Modifier cet acte</a>
  <a href="/actes/naissance/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-primary">Générer le PDF officiel</a>
  <?php endif; ?>
</div>
