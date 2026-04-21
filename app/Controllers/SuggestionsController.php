<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;

class SuggestionsController extends Controller
{
    public function search(Request $request): void
    {
        $type = $request->get('type', '');
        $q    = trim($request->get('q', ''));

        if (strlen($q) < 2 || !in_array($type, ['naissance', 'mariage', 'deces'], true)) {
            $this->json([]);
        }

        $arrondissementId = $this->arrondissementId();
        $like = '%' . $q . '%';

        $results = match ($type) {
            'naissance' => $this->searchNaissances($like, $arrondissementId),
            'mariage'   => $this->searchMariages($like, $arrondissementId),
            'deces'     => $this->searchDeces($like, $arrondissementId),
            default     => [],
        };

        $this->json($results);
    }

    private function searchNaissances(string $like, ?int $arrId): array
    {
        $sql = "SELECT CONCAT(enfant_nom, ' ', enfant_prenom) AS label, enfant_nom AS nom
                FROM naissances
                WHERE statut != 'ANNULÉ' AND (enfant_nom LIKE ? OR enfant_prenom LIKE ?)";
        $params = [$like, $like];
        if ($arrId !== null) { $sql .= ' AND arrondissement_id = ?'; $params[] = $arrId; }
        $sql .= ' GROUP BY enfant_nom, enfant_prenom ORDER BY enfant_nom LIMIT 8';
        return $this->fetch($sql, $params);
    }

    private function searchMariages(string $like, ?int $arrId): array
    {
        $sql = "SELECT CONCAT(epoux_nom, ' ', epoux_prenom) AS label, epoux_nom AS nom
                FROM mariages
                WHERE statut != 'ANNULÉ' AND (epoux_nom LIKE ? OR epoux_prenom LIKE ? OR epouse_nom LIKE ? OR epouse_prenom LIKE ?)";
        $params = [$like, $like, $like, $like];
        if ($arrId !== null) { $sql .= ' AND arrondissement_id = ?'; $params[] = $arrId; }
        $sql .= ' GROUP BY epoux_nom, epoux_prenom ORDER BY epoux_nom LIMIT 8';
        return $this->fetch($sql, $params);
    }

    private function searchDeces(string $like, ?int $arrId): array
    {
        $sql = "SELECT CONCAT(defunt_nom, ' ', defunt_prenom) AS label, defunt_nom AS nom
                FROM deces
                WHERE statut != 'ANNULÉ' AND (defunt_nom LIKE ? OR defunt_prenom LIKE ?)";
        $params = [$like, $like];
        if ($arrId !== null) { $sql .= ' AND arrondissement_id = ?'; $params[] = $arrId; }
        $sql .= ' GROUP BY defunt_nom, defunt_prenom ORDER BY defunt_nom LIMIT 8';
        return $this->fetch($sql, $params);
    }

    private function fetch(string $sql, array $params): array
    {
        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute($params);
        return array_column($stmt->fetchAll(), 'label');
    }
}
