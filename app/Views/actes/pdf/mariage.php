<?php
/** @var array  $acte */
/** @var array  $temoins */
/** @var array  $config */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #111; margin: 0; padding: 40px; }
  .header { text-align: center; border-bottom: 2px solid #111; padding-bottom: 16px; margin-bottom: 24px; }
  .title { font-size: 16pt; font-weight: bold; text-transform: uppercase; margin: 8px 0; }
  .acte-num { font-size: 10pt; color: #555; margin-top: 4px; }
  .section { margin-top: 20px; }
  .section-title { font-weight: bold; text-transform: uppercase; font-size: 9pt; border-bottom: 1px solid #999; padding-bottom: 4px; margin-bottom: 10px; color: #555; letter-spacing: 0.05em; }
  table.fields { width: 100%; border-collapse: collapse; }
  table.fields td { padding: 4px 8px; vertical-align: top; width: 50%; }
  .field-label { font-weight: bold; font-size: 9pt; }
  .field-value { margin-left: 16px; }
  .footer { margin-top: 48px; display: flex; justify-content: space-between; }
  .signature-block { text-align: center; }
  .signature-line { border-top: 1px solid #111; margin-top: 48px; padding-top: 4px; font-size: 9pt; }
</style>
</head>
<body>
<div class="header">
  <div style="font-size:9pt;text-transform:uppercase;letter-spacing:0.08em;">République du Bénin</div>
  <div style="font-size:9pt;">Département du Littoral &mdash; <?= htmlspecialchars($config['mairie_nom']) ?></div>
  <div class="title">Acte de Mariage</div>
  <div class="acte-num">N° <?= htmlspecialchars($acte['numero_acte']) ?>/<?= $acte['annee'] ?> &mdash; <?= htmlspecialchars($acte['arrondissement_nom'] ?? '') ?></div>
</div>

<?php
$mois = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$jour = date('d', strtotime($acte['date_mariage']));
$m    = (int) date('m', strtotime($acte['date_mariage']));
$an   = date('Y', strtotime($acte['date_mariage']));
?>
<p>
  L'an <?= $an ?>, le <?= $jour ?> <?= $mois[$m] ?><?= $acte['heure_mariage'] ? ' à ' . htmlspecialchars($acte['heure_mariage']) : '' ?>,
  à <?= htmlspecialchars($acte['lieu_celebration'] ?? $acte['arrondissement_nom'] ?? '') ?>,
  ont été unis en mariage <?= $acte['type_mariage'] === 'POLYGAMIQUE' ? '(mariage polygamique)' : '' ?> :
</p>

<div class="section">
  <div class="section-title">Époux</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Nom :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_nom']) ?></div></td>
      <td><div class="field-label">Prénom(s) :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_prenom']) ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Date de naissance :</div><div class="field-value"><?= $acte['epoux_date_naissance'] ? date('d/m/Y', strtotime($acte['epoux_date_naissance'])) : '—' ?></div></td>
      <td><div class="field-label">Lieu de naissance :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_lieu_naissance'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Nationalité :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_nationalite'] ?? '') ?></div></td>
      <td><div class="field-label">Profession :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_profession'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Domicile :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_domicile'] ?? '') ?></div></td>
      <td><div class="field-label">État civil antérieur :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_statut_anterieur'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Père :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_pere_nom_prenom'] ?? '') ?></div></td>
      <td><div class="field-label">Mère :</div><div class="field-value"><?= htmlspecialchars($acte['epoux_mere_nom_prenom'] ?? '') ?></div></td>
    </tr>
  </table>
</div>

<div class="section">
  <div class="section-title">Épouse</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Nom :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_nom']) ?></div></td>
      <td><div class="field-label">Prénom(s) :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_prenom']) ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Date de naissance :</div><div class="field-value"><?= $acte['epouse_date_naissance'] ? date('d/m/Y', strtotime($acte['epouse_date_naissance'])) : '—' ?></div></td>
      <td><div class="field-label">Lieu de naissance :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_lieu_naissance'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Nationalité :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_nationalite'] ?? '') ?></div></td>
      <td><div class="field-label">Profession :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_profession'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Domicile :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_domicile'] ?? '') ?></div></td>
      <td><div class="field-label">État civil antérieur :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_statut_anterieur'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Père :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_pere_nom_prenom'] ?? '') ?></div></td>
      <td><div class="field-label">Mère :</div><div class="field-value"><?= htmlspecialchars($acte['epouse_mere_nom_prenom'] ?? '') ?></div></td>
    </tr>
  </table>
</div>

<?php if (!empty($acte['epouses_supplementaires'])): ?>
<?php foreach ($acte['epouses_supplementaires'] as $es): ?>
<div class="section">
  <div class="section-title">Épouse n°<?= $es['ordre_epouse'] ?> (polygamie)</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Nom :</div><div class="field-value"><?= htmlspecialchars($es['epouse_nom']) ?></div></td>
      <td><div class="field-label">Prénom(s) :</div><div class="field-value"><?= htmlspecialchars($es['epouse_prenom']) ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Date de naissance :</div><div class="field-value"><?= $es['epouse_date_naissance'] ? date('d/m/Y', strtotime($es['epouse_date_naissance'])) : '—' ?></div></td>
      <td><div class="field-label">Lieu de naissance :</div><div class="field-value"><?= htmlspecialchars($es['epouse_lieu_naissance'] ?? '') ?></div></td>
    </tr>
  </table>
</div>
<?php endforeach; ?>
<?php endif; ?>

<div class="section">
  <div class="section-title">Régime &amp; publications</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Régime matrimonial :</div><div class="field-value"><?= htmlspecialchars($acte['regime_matrimonial'] ?? '') ?></div></td>
      <td><div class="field-label">Publication des bans :</div><div class="field-value"><?= $acte['date_publication_bans'] ? date('d/m/Y', strtotime($acte['date_publication_bans'])) : '—' ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Opposition reçue :</div><div class="field-value"><?= $acte['opposition_recue'] ? 'Oui' : 'Non' ?></div></td>
      <td><div class="field-label">Type de mariage :</div><div class="field-value"><?= htmlspecialchars($acte['type_mariage']) ?></div></td>
    </tr>
  </table>
</div>

<?php if (!empty($temoins)): ?>
<div class="section">
  <div class="section-title">Témoins</div>
  <table class="fields">
    <?php foreach ($temoins as $t): ?>
    <tr>
      <td><div class="field-label">Témoin n°<?= $t['ordre'] ?> :</div><div class="field-value"><?= htmlspecialchars($t['nom'] . ' ' . $t['prenom']) ?></div></td>
      <td><div class="field-label">Domicile :</div><div class="field-value"><?= htmlspecialchars($t['domicile'] ?? '') ?></div></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<?php if (!empty($acte['observations'])): ?>
<div class="section">
  <div class="section-title">Mentions marginales</div>
  <p><?= htmlspecialchars($acte['observations']) ?></p>
</div>
<?php endif; ?>

<div class="footer">
  <div>
    <p style="font-size:9pt;">Dressé le <?= date('d/m/Y', strtotime($acte['date_declaration'])) ?></p>
    <p style="font-size:9pt;">à <?= htmlspecialchars($acte['arrondissement_nom'] ?? '') ?></p>
  </div>
  <div class="signature-block">
    <p style="font-size:9pt;">L'Officier de l'État Civil</p>
    <div class="signature-line"><?= htmlspecialchars(($acte['officier_prenom'] ?? '') . ' ' . ($acte['officier_nom'] ?? '')) ?></div>
  </div>
</div>
</body>
</html>
