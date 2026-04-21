<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Naissance;
use App\Models\Mariage;
use App\Models\Deces;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $arrondissementId = $this->arrondissementId(); // null pour les admins
        $annee            = (int) date('Y');

        $statsNaissances = Naissance::countByArrondissement($arrondissementId, $annee);
        $statsMariages   = Mariage::countByType($arrondissementId, $annee);
        $statsDeces      = Deces::countBySexe($arrondissementId, $annee);
        $activiteRecente = AuditLog::recent($arrondissementId, 10);

        // Évolution mensuelle de l'année en cours
        $evolutionMensuelle = $this->evolutionMensuelle($arrondissementId, $annee);

        $this->render('dashboard/index', [
            'title'               => 'Tableau de bord',
            'stats_naissances'    => $statsNaissances,
            'stats_mariages'      => $statsMariages,
            'stats_deces'         => $statsDeces,
            'activite_recente'    => $activiteRecente,
            'evolution_mensuelle' => $evolutionMensuelle,
            'annee'               => $annee,
            'flash'               => $this->getFlash(),
        ]);
    }

    private function evolutionMensuelle(?int $arrondissementId, int $annee): array
    {
        $db     = \App\Core\Database::getConnection();
        $where  = 'YEAR(created_at) = ?';
        $values = [$annee];

        if ($arrondissementId !== null) {
            $where  .= ' AND arrondissement_id = ?';
            $values[] = $arrondissementId;
        }

        $mois = [];
        for ($m = 1; $m <= 12; $m++) {
            $mois[$m] = ['naissances' => 0, 'mariages' => 0, 'deces' => 0];
        }

        foreach (['naissances', 'mariages', 'deces'] as $table) {
            $stmt = $db->prepare(
                "SELECT MONTH(created_at) AS mois, COUNT(*) AS total
                 FROM {$table} WHERE {$where} AND statut = 'ACTIF'
                 GROUP BY MONTH(created_at)"
            );
            $stmt->execute($values);
            foreach ($stmt->fetchAll() as $row) {
                $mois[(int) $row['mois']][$table] = (int) $row['total'];
            }
        }

        return $mois;
    }
}
