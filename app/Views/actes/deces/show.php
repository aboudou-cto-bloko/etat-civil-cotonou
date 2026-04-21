<?php
/** @var array      $acte */
/** @var array      $temoins */
$statusMap   = ['ACTIF' => 'badge-green', 'ANNULÉ' => 'badge-red', 'RECTIFIÉ' => 'badge-orange'];
$statusClass = $statusMap[$acte['statut'] ?? 'ACTIF'] ?? 'badge-neutral';
$canEdit     = in_array($_SESSION['user']['role_code'] ?? '', ['admin', 'superviseur']);
$empty       = fn($v) => !empty($v) ? \App\Core\View::e($v) : '<span class="detail-value--empty">Non renseigné</span>';
$date        = fn($v) => $v ? date('d/m/Y', strtotime($v)) : '<span class="detail-value--empty">—</span>';
?>

<div class="breadcrumb">
  <a href="/deces">Décès</a>
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
      <h1 class="page-title"><?= \App\Core\View::e($acte['defunt_nom'] . ' ' . $acte['defunt_prenom']) ?></h1>
      <p class="page-subtitle">
        Acte de décès &mdash; <?= \App\Core\View::e($acte['arrondissement_nom'] ?? '') ?>
        &mdash; Décédé le <?= date('d/m/Y', strtotime($acte['date_deces'])) ?>
      </p>
    </div>
    <?php if ($canEdit && $acte['statut'] === 'ACTIF'): ?>
    <div class="page-actions">
      <a href="/actes/deces/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-secondary">Générer PDF</a>
      <a href="/deces/<?= \App\Core\View::e($acte['id']) ?>/modifier" class="btn btn-primary">Modifier</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="card mb-5">
  <div class="detail-grid">

    <!-- CIRCONSTANCES -->
    <div class="detail-section-title">Circonstances du décès</div>

    <div class="detail-row">
      <div class="detail-key">Date et heure du décès</div>
      <div class="detail-value"><?= date('d/m/Y à H:i', strtotime($acte['date_deces'])) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Lieu du décès</div>
      <div class="detail-value"><?= $empty($acte['lieu_deces']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Cause du décès</div>
      <div class="detail-value"><?= $empty($acte['cause_deces']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Certificat médical</div>
      <div class="detail-value">
        <?= $acte['certificat_medical_fourni']
          ? '<span class="badge badge-green">Fourni</span>' . ($acte['numero_certificat_medical'] ? ' &mdash; ' . \App\Core\View::e($acte['numero_certificat_medical']) : '')
          : '<span class="badge badge-neutral">Non fourni</span>' ?>
      </div>
    </div>

    <!-- DÉFUNT -->
    <div class="detail-section-title">Identité du défunt</div>

    <div class="detail-row">
      <div class="detail-key">Nom & Prénom</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['defunt_nom'] . ' ' . $acte['defunt_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Sexe</div>
      <div class="detail-value">
        <?= $acte['defunt_sexe'] === 'M'
          ? '<span class="badge badge-blue">Masculin</span>'
          : '<span class="badge badge-red">Féminin</span>' ?>
      </div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date de naissance</div>
      <div class="detail-value"><?= $date($acte['defunt_date_naissance']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Lieu de naissance</div>
      <div class="detail-value"><?= $empty($acte['defunt_lieu_naissance']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Nationalité</div>
      <div class="detail-value"><?= $empty($acte['defunt_nationalite']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Profession</div>
      <div class="detail-value"><?= $empty($acte['defunt_profession']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Domicile</div>
      <div class="detail-value"><?= $empty($acte['defunt_domicile']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Situation matrimoniale</div>
      <div class="detail-value"><?= $empty($acte['defunt_situation_matrimoniale']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Filiation (père)</div>
      <div class="detail-value"><?= $empty($acte['defunt_pere_nom_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Filiation (mère)</div>
      <div class="detail-value"><?= $empty($acte['defunt_mere_nom_prenom']) ?></div>
    </div>

    <!-- DÉCLARANT -->
    <div class="detail-section-title">Déclarant</div>

    <div class="detail-row">
      <div class="detail-key">Nom & Prénom</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['declarant_nom'] . ' ' . $acte['declarant_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Qualité</div>
      <div class="detail-value"><?= $empty($acte['declarant_qualite']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Domicile</div>
      <div class="detail-value"><?= $empty($acte['declarant_domicile']) ?></div>
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

    <!-- REGISTRE -->
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
      <div class="detail-key">Observations</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['observations']) ?></div>
    </div>
    <?php endif; ?>

  </div>
</div>

<div style="display:flex;gap:var(--space-5);">
  <a href="/deces" class="btn btn-ghost">&larr; Retour à la liste</a>
  <?php if ($canEdit && $acte['statut'] === 'ACTIF'): ?>
  <a href="/deces/<?= \App\Core\View::e($acte['id']) ?>/modifier" class="btn btn-secondary">Modifier cet acte</a>
  <a href="/actes/deces/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-primary">Générer le PDF officiel</a>
  <?php endif; ?>
</div>
