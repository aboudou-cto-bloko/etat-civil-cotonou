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
  .subtitle { font-size: 11pt; }
  .acte-num { font-size: 10pt; color: #555; margin-top: 4px; }
  .section { margin-top: 20px; }
  .section-title { font-weight: bold; text-transform: uppercase; font-size: 9pt; border-bottom: 1px solid #999; padding-bottom: 4px; margin-bottom: 10px; color: #555; letter-spacing: 0.05em; }
  .field { margin-bottom: 8px; }
  .field-label { font-weight: bold; font-size: 9pt; }
  .field-value { margin-left: 16px; }
  table.fields { width: 100%; border-collapse: collapse; }
  table.fields td { padding: 4px 8px; vertical-align: top; width: 50%; }
  .footer { margin-top: 48px; display: flex; justify-content: space-between; align-items: flex-end; }
  .signature-block { text-align: center; }
  .signature-line { border-top: 1px solid #111; margin-top: 48px; padding-top: 4px; font-size: 9pt; }
  .qr-block { text-align: center; }
  .qr-block img { width: 72px; height: 72px; }
  .qr-label { font-size: 6pt; color: #777; margin-top: 4px; }
</style>
</head>
<body>
<div class="header">
  <div style="font-size:9pt;text-transform:uppercase;letter-spacing:0.08em;">République du Bénin</div>
  <div style="font-size:9pt;">Département du Littoral &mdash; <?= htmlspecialchars($config['mairie_nom']) ?></div>
  <div class="title">Acte de Naissance</div>
  <div class="acte-num">N° <?= htmlspecialchars($acte['numero_acte']) ?>/<?= $acte['annee'] ?> &mdash; <?= htmlspecialchars($acte['arrondissement_nom'] ?? '') ?></div>
</div>

<p>L'an <?= date('Y', strtotime($acte['date_naissance'])) ?>, le <?= date('d', strtotime($acte['date_naissance'])) ?> <?php
  $mois = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
  echo $mois[(int)date('m', strtotime($acte['date_naissance']))];
?> à <?= date('H:i', strtotime($acte['date_naissance'])) ?>, est né(e) à <?= htmlspecialchars($acte['lieu_naissance_commune']) ?><?= $acte['lieu_naissance_localite'] ? ', ' . htmlspecialchars($acte['lieu_naissance_localite']) : '' ?> :</p>

<div class="section">
  <div class="section-title">Identité de l'enfant</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Nom :</div><div class="field-value"><?= htmlspecialchars($acte['enfant_nom']) ?></div></td>
      <td><div class="field-label">Prénom(s) :</div><div class="field-value"><?= htmlspecialchars($acte['enfant_prenom']) ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Sexe :</div><div class="field-value"><?= $acte['enfant_sexe'] === 'M' ? 'Masculin' : 'Féminin' ?></div></td>
      <td><div class="field-label">Naissance multiple :</div><div class="field-value"><?= $acte['enfant_est_jumeau'] ? 'Oui — ' . $acte['ordre_jumeau'] . 'er' : 'Non' ?></div></td>
    </tr>
  </table>
</div>

<div class="section">
  <div class="section-title">Père</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Nom & Prénom :</div><div class="field-value"><?= htmlspecialchars(($acte['pere_nom'] ?? '') . ' ' . ($acte['pere_prenom'] ?? '')) ?></div></td>
      <td><div class="field-label">Nationalité :</div><div class="field-value"><?= htmlspecialchars($acte['pere_nationalite'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Profession :</div><div class="field-value"><?= htmlspecialchars($acte['pere_profession'] ?? '') ?></div></td>
      <td><div class="field-label">Domicile :</div><div class="field-value"><?= htmlspecialchars($acte['pere_domicile'] ?? '') ?></div></td>
    </tr>
  </table>
</div>

<div class="section">
  <div class="section-title">Mère</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Nom & Prénom :</div><div class="field-value"><?= htmlspecialchars($acte['mere_nom'] . ' ' . $acte['mere_prenom']) ?></div></td>
      <td><div class="field-label">Nationalité :</div><div class="field-value"><?= htmlspecialchars($acte['mere_nationalite'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Profession :</div><div class="field-value"><?= htmlspecialchars($acte['mere_profession'] ?? '') ?></div></td>
      <td><div class="field-label">Domicile :</div><div class="field-value"><?= htmlspecialchars($acte['mere_domicile'] ?? '') ?></div></td>
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
  <?php if (!empty($config['qr_data_uri'])): ?>
  <div class="qr-block">
    <img src="<?= $config['qr_data_uri'] ?>">
    <div class="qr-label">Vérification officielle</div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
