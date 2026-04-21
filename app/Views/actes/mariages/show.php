<?php
/** @var array      $acte */
/** @var array      $temoins */
$statusMap   = ['ACTIF' => 'badge-green', 'ANNULÉ' => 'badge-red', 'DISSOUS' => 'badge-orange', 'RECTIFIÉ' => 'badge-orange'];
$statusClass = $statusMap[$acte['statut'] ?? 'ACTIF'] ?? 'badge-neutral';
$canEdit     = in_array($_SESSION['user']['role_code'] ?? '', ['admin', 'superviseur']);
$empty       = fn($v) => !empty($v) ? \App\Core\View::e($v) : '<span class="detail-value--empty">Non renseigné</span>';
$date        = fn($v) => $v ? date('d/m/Y', strtotime($v)) : '<span class="detail-value--empty">—</span>';
?>

<div class="breadcrumb">
  <a href="/mariages">Mariages</a>
  <span class="breadcrumb-sep">/</span>
  <span class="breadcrumb-current">Acte n°<?= \App\Core\View::e($acte['numero_acte']) ?>/<?= $acte['annee'] ?></span>
</div>

<div class="page-header">
  <div class="page-header-row">
    <div>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
        <span class="acte-number">N° <?= \App\Core\View::e($acte['numero_acte']) ?>/<?= $acte['annee'] ?></span>
        <span class="badge <?= $statusClass ?>"><?= \App\Core\View::e($acte['statut']) ?></span>
        <span class="badge <?= $acte['type_mariage'] === 'POLYGAMIQUE' ? 'badge-orange' : 'badge-neutral' ?>">
          <?= \App\Core\View::e($acte['type_mariage']) ?>
        </span>
      </div>
      <h1 class="page-title">
        <?= \App\Core\View::e($acte['epoux_nom'] . ' ' . $acte['epoux_prenom']) ?>
        <span style="color:var(--color-text-tertiary);font-weight:400;">&amp;</span>
        <?= \App\Core\View::e($acte['epouse_nom'] . ' ' . $acte['epouse_prenom']) ?>
      </h1>
      <p class="page-subtitle">
        Acte de mariage &mdash; <?= \App\Core\View::e($acte['arrondissement_nom'] ?? '') ?>
        &mdash; <?= date('d/m/Y', strtotime($acte['date_mariage'])) ?>
      </p>
    </div>
    <?php if ($canEdit && $acte['statut'] === 'ACTIF'): ?>
    <div class="page-actions">
      <a href="/actes/mariage/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-secondary">Générer PDF</a>
      <a href="/mariages/<?= \App\Core\View::e($acte['id']) ?>/modifier" class="btn btn-primary"
         data-confirm="Modifier cet acte de mariage ?"
         data-confirm-body="Toute modification sera tracée dans le journal d'audit."
         data-confirm-label="Continuer"
         data-confirm-variant="warning">Modifier</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- CÉRÉMONIE -->
<div class="card mb-5">
  <div class="detail-grid">
    <div class="detail-section-title">Cérémonie</div>

    <div class="detail-row">
      <div class="detail-key">Date du mariage</div>
      <div class="detail-value"><?= date('d/m/Y', strtotime($acte['date_mariage'])) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Heure</div>
      <div class="detail-value"><?= $empty($acte['heure_mariage']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Lieu de célébration</div>
      <div class="detail-value"><?= $empty($acte['lieu_celebration']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Régime matrimonial</div>
      <div class="detail-value"><?= $empty($acte['regime_matrimonial']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Publication des bans</div>
      <div class="detail-value"><?= $date($acte['date_publication_bans']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Opposition reçue</div>
      <div class="detail-value">
        <?= $acte['opposition_recue']
          ? '<span class="badge badge-red">Oui</span>'
          : '<span class="badge badge-neutral">Non</span>' ?>
      </div>
    </div>

    <!-- ÉPOUX -->
    <div class="detail-section-title">Époux</div>

    <div class="detail-row">
      <div class="detail-key">Nom & Prénom</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['epoux_nom'] . ' ' . $acte['epoux_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date de naissance</div>
      <div class="detail-value"><?= $date($acte['epoux_date_naissance']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Lieu de naissance</div>
      <div class="detail-value"><?= $empty($acte['epoux_lieu_naissance']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Nationalité</div>
      <div class="detail-value"><?= $empty($acte['epoux_nationalite']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Profession</div>
      <div class="detail-value"><?= $empty($acte['epoux_profession']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Domicile</div>
      <div class="detail-value"><?= $empty($acte['epoux_domicile']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Statut antérieur</div>
      <div class="detail-value"><?= $empty($acte['epoux_statut_anterieur']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Filiation (père)</div>
      <div class="detail-value"><?= $empty($acte['epoux_pere_nom_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Filiation (mère)</div>
      <div class="detail-value"><?= $empty($acte['epoux_mere_nom_prenom']) ?></div>
    </div>

    <!-- ÉPOUSE -->
    <div class="detail-section-title">Épouse</div>

    <div class="detail-row">
      <div class="detail-key">Nom & Prénom</div>
      <div class="detail-value"><?= \App\Core\View::e($acte['epouse_nom'] . ' ' . $acte['epouse_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date de naissance</div>
      <div class="detail-value"><?= $date($acte['epouse_date_naissance']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Lieu de naissance</div>
      <div class="detail-value"><?= $empty($acte['epouse_lieu_naissance']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Nationalité</div>
      <div class="detail-value"><?= $empty($acte['epouse_nationalite']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Profession</div>
      <div class="detail-value"><?= $empty($acte['epouse_profession']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Domicile</div>
      <div class="detail-value"><?= $empty($acte['epouse_domicile']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Statut antérieur</div>
      <div class="detail-value"><?= $empty($acte['epouse_statut_anterieur']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Filiation (père)</div>
      <div class="detail-value"><?= $empty($acte['epouse_pere_nom_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Filiation (mère)</div>
      <div class="detail-value"><?= $empty($acte['epouse_mere_nom_prenom']) ?></div>
    </div>

    <?php if (!empty($acte['epouses_supplementaires'])): ?>
    <?php foreach ($acte['epouses_supplementaires'] as $es): ?>
    <div class="detail-section-title">Épouse n°<?= $es['ordre_epouse'] ?> (polygamie)</div>
    <div class="detail-row">
      <div class="detail-key">Nom & Prénom</div>
      <div class="detail-value"><?= \App\Core\View::e($es['epouse_nom'] . ' ' . $es['epouse_prenom']) ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Date de naissance</div>
      <div class="detail-value"><?= $date($es['epouse_date_naissance']) ?></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

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
      <div class="detail-key">Consentement parents époux</div>
      <div class="detail-value"><?= $acte['consentement_parents_epoux'] ? 'Oui' : 'Non' ?></div>
    </div>
    <div class="detail-row">
      <div class="detail-key">Consentement parents épouse</div>
      <div class="detail-value"><?= $acte['consentement_parents_epouse'] ? 'Oui' : 'Non' ?></div>
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
  <a href="/mariages" class="btn btn-ghost">&larr; Retour à la liste</a>
  <?php if ($canEdit && $acte['statut'] === 'ACTIF'): ?>
  <a href="/mariages/<?= \App\Core\View::e($acte['id']) ?>/modifier" class="btn btn-secondary"
     data-confirm="Modifier cet acte de mariage ?"
     data-confirm-body="Toute modification sera tracée dans le journal d'audit."
     data-confirm-label="Continuer"
     data-confirm-variant="warning">Modifier cet acte</a>
  <a href="/actes/mariage/<?= \App\Core\View::e($acte['id']) ?>/pdf" class="btn btn-primary">Générer le PDF officiel</a>
  <?php endif; ?>
</div>
