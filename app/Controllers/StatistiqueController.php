<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Models\Naissance;
use App\Models\Mariage;
use App\Models\Deces;
use App\Models\AuditLog;

class StatistiqueController extends Controller
{
    public function index(Request $request): void
    {
        $arrondissementId = $this->arrondissementId();
        $annee            = (int) ($request->get('annee') ?: date('Y'));
        $dateDebut        = $request->get('date_debut', "{$annee}-01-01");
        $dateFin          = $request->get('date_fin', "{$annee}-12-31");

        $naissances = Naissance::countByArrondissement($arrondissementId, $annee);
        $mariages   = Mariage::countByType($arrondissementId, $annee);
        $deces      = Deces::countBySexe($arrondissementId, $annee);

        $statsParMois    = $this->statsMensuelles($arrondissementId, $annee);
        $statsByUser     = AuditLog::statsByUser($arrondissementId, $dateDebut, $dateFin);

        // Comparaison par arrondissement (admins uniquement)
        $statsArrondissements = null;
        if ($arrondissementId === null) {
            $statsArrondissements = $this->statsParArrondissement($annee);
        }

        $this->render('statistiques/index', [
            'title'                  => 'Statistiques',
            'annee'                  => $annee,
            'date_debut'             => $dateDebut,
            'date_fin'               => $dateFin,
            'stats_naissances'       => $naissances,
            'stats_mariages'         => $mariages,
            'stats_deces'            => $deces,
            'stats_par_mois'         => $statsParMois,
            'stats_by_user'          => $statsByUser,
            'stats_arrondissements'  => $statsArrondissements,
        ]);
    }

    public function export(Request $request): void
    {
        // Export CSV basique
        $arrondissementId = $this->arrondissementId();
        $annee            = (int) ($request->get('annee') ?: date('Y'));

        $data = $this->statsMensuelles($arrondissementId, $annee);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="statistiques_' . $annee . '.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
        fputcsv($out, ['Mois', 'Naissances', 'Mariages', 'Décès'], ';');

        $moisLabels = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                       'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        foreach ($data as $mois => $stats) {
            fputcsv($out, [
                $moisLabels[$mois],
                $stats['naissances'],
                $stats['mariages'],
                $stats['deces'],
            ], ';');
        }

        fclose($out);
        exit;
    }

    private function statsMensuelles(?int $arrondissementId, int $annee): array
    {
        $mois = [];
        for ($m = 1; $m <= 12; $m++) {
            $mois[$m] = ['naissances' => 0, 'mariages' => 0, 'deces' => 0];
        }

        $db     = Database::getConnection();
        $where  = "YEAR(created_at) = ? AND statut = 'ACTIF'";
        $values = [$annee];

        if ($arrondissementId !== null) {
            $where   .= ' AND arrondissement_id = ?';
            $values[] = $arrondissementId;
        }

        foreach (['naissances', 'mariages', 'deces'] as $table) {
            $stmt = $db->prepare(
                "SELECT MONTH(created_at) AS m, COUNT(*) AS total FROM {$table} WHERE {$where} GROUP BY MONTH(created_at)"
            );
            $stmt->execute($values);
            foreach ($stmt->fetchAll() as $row) {
                $mois[(int) $row['m']][$table] = (int) $row['total'];
            }
        }

        return $mois;
    }

    private function statsParArrondissement(int $annee): array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT a.numero, a.nom,
                    (SELECT COUNT(*) FROM naissances n WHERE n.arrondissement_id = a.id AND n.annee = ? AND n.statut = 'ACTIF') AS naissances,
                    (SELECT COUNT(*) FROM mariages m  WHERE m.arrondissement_id = a.id AND m.annee = ? AND m.statut = 'ACTIF') AS mariages,
                    (SELECT COUNT(*) FROM deces d     WHERE d.arrondissement_id = a.id AND d.annee = ? AND d.statut = 'ACTIF') AS deces
             FROM arrondissements a
             ORDER BY a.numero ASC"
        );
        $stmt->execute([$annee, $annee, $annee]);
        return $stmt->fetchAll();
    }
}
