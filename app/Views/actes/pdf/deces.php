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
  <div class="title">Acte de Décès</div>
  <div class="acte-num">N° <?= htmlspecialchars($acte['numero_acte']) ?>/<?= $acte['annee'] ?> &mdash; <?= htmlspecialchars($acte['arrondissement_nom'] ?? '') ?></div>
</div>

<?php
$mois  = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$ts    = strtotime($acte['date_deces']);
$jour  = date('d', $ts);
$m     = (int) date('m', $ts);
$an    = date('Y', $ts);
$heure = date('H:i', $ts);
?>
<p>
  L'an <?= $an ?>, le <?= $jour ?> <?= $mois[$m] ?> à <?= $heure ?>,
  est décédé(e) à <?= htmlspecialchars($acte['lieu_deces']) ?> :
</p>

<div class="section">
  <div class="section-title">Identité du défunt</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Nom :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_nom']) ?></div></td>
      <td><div class="field-label">Prénom(s) :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_prenom']) ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Sexe :</div><div class="field-value"><?= $acte['defunt_sexe'] === 'M' ? 'Masculin' : 'Féminin' ?></div></td>
      <td><div class="field-label">Situation matrimoniale :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_situation_matrimoniale'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Date de naissance :</div><div class="field-value"><?= $acte['defunt_date_naissance'] ? date('d/m/Y', strtotime($acte['defunt_date_naissance'])) : '—' ?></div></td>
      <td><div class="field-label">Lieu de naissance :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_lieu_naissance'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Nationalité :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_nationalite'] ?? '') ?></div></td>
      <td><div class="field-label">Profession :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_profession'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Domicile :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_domicile'] ?? '') ?></div></td>
      <td></td>
    </tr>
    <tr>
      <td><div class="field-label">Père :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_pere_nom_prenom'] ?? '') ?></div></td>
      <td><div class="field-label">Mère :</div><div class="field-value"><?= htmlspecialchars($acte['defunt_mere_nom_prenom'] ?? '') ?></div></td>
    </tr>
  </table>
</div>

<div class="section">
  <div class="section-title">Circonstances du décès</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Date et heure :</div><div class="field-value"><?= date('d/m/Y à H:i', strtotime($acte['date_deces'])) ?></div></td>
      <td><div class="field-label">Lieu :</div><div class="field-value"><?= htmlspecialchars($acte['lieu_deces']) ?></div></td>
    </tr>
    <?php if ($acte['cause_deces']): ?>
    <tr>
      <td><div class="field-label">Cause :</div><div class="field-value"><?= htmlspecialchars($acte['cause_deces']) ?></div></td>
      <td><div class="field-label">Certificat médical :</div><div class="field-value"><?= $acte['certificat_medical_fourni'] ? 'Fourni' . ($acte['numero_certificat_medical'] ? ' — ' . htmlspecialchars($acte['numero_certificat_medical']) : '') : 'Non fourni' ?></div></td>
    </tr>
    <?php else: ?>
    <tr>
      <td><div class="field-label">Certificat médical :</div><div class="field-value"><?= $acte['certificat_medical_fourni'] ? 'Fourni' . ($acte['numero_certificat_medical'] ? ' — ' . htmlspecialchars($acte['numero_certificat_medical']) : '') : 'Non fourni' ?></div></td>
      <td></td>
    </tr>
    <?php endif; ?>
  </table>
</div>

<div class="section">
  <div class="section-title">Déclarant</div>
  <table class="fields">
    <tr>
      <td><div class="field-label">Nom &amp; Prénom :</div><div class="field-value"><?= htmlspecialchars($acte['declarant_nom'] . ' ' . ($acte['declarant_prenom'] ?? '')) ?></div></td>
      <td><div class="field-label">Qualité :</div><div class="field-value"><?= htmlspecialchars($acte['declarant_qualite'] ?? '') ?></div></td>
    </tr>
    <tr>
      <td><div class="field-label">Domicile :</div><div class="field-value"><?= htmlspecialchars($acte['declarant_domicile'] ?? '') ?></div></td>
      <td></td>
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
