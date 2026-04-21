<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Deces extends Model
{
    protected static string $table = 'deces';

    public static function search(array $filters, ?int $arrondissementId = null, int $perPage = 20, int $page = 1, string $sort = 'created_at', string $direction = 'desc'): array
    {
        $allowedSorts = ['defunt_nom', 'date_deces', 'numero_acte', 'created_at'];
        $sort      = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $direction = $direction === 'asc' ? 'ASC' : 'DESC';
        $where  = ['d.statut != ?'];
        $values = ['ANNULÉ'];

        if ($arrondissementId !== null) {
            $where[]  = 'd.arrondissement_id = ?';
            $values[] = $arrondissementId;
        }

        if (!empty($filters['nom'])) {
            $where[]  = "(d.defunt_nom LIKE ? OR d.defunt_prenom LIKE ?)";
            $like     = '%' . $filters['nom'] . '%';
            $values[] = $like;
            $values[] = $like;
        }

        if (!empty($filters['numero_acte'])) {
            $where[]  = 'd.numero_acte = ?';
            $values[] = $filters['numero_acte'];
        }

        if (!empty($filters['annee'])) {
            $where[]  = 'd.annee = ?';
            $values[] = (int) $filters['annee'];
        }

        if (!empty($filters['date_debut'])) {
            $where[]  = 'DATE(d.date_deces) >= ?';
            $values[] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $where[]  = 'DATE(d.date_deces) <= ?';
            $values[] = $filters['date_fin'];
        }

        $whereStr = implode(' AND ', $where);

        $countStmt = self::db()->prepare("SELECT COUNT(*) FROM deces d WHERE {$whereStr}");
        $countStmt->execute($values);
        $total  = (int) $countStmt->fetchColumn();
        $offset = ($page - 1) * $perPage;

        $stmt = self::db()->prepare(
            "SELECT d.*, a.nom AS arrondissement_nom, a.numero AS arrondissement_numero,
                    u.nom AS enregistre_par_nom, u.prenom AS enregistre_par_prenom
             FROM deces d
             JOIN arrondissements a ON d.arrondissement_id = a.id
             JOIN users u ON d.enregistre_par = u.id
             WHERE {$whereStr}
             ORDER BY d.{$sort} {$direction}
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($values);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    public static function findWithDetails(string $id): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT d.*, a.nom AS arrondissement_nom, a.numero AS arrondissement_numero,
                    u.nom AS enregistre_par_nom, u.prenom AS enregistre_par_prenom,
                    oe.nom AS officier_nom, oe.prenom AS officier_prenom
             FROM deces d
             JOIN arrondissements a ON d.arrondissement_id = a.id
             JOIN users u ON d.enregistre_par = u.id
             JOIN users oe ON d.officier_etat_civil_id = oe.id
             WHERE d.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function prochainNumeroActe(int $arrondissementId, int $annee): string
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) + 1 FROM deces WHERE arrondissement_id = ? AND annee = ?"
        );
        $stmt->execute([$arrondissementId, $annee]);
        return str_pad((string) ((int) $stmt->fetchColumn()), 4, '0', STR_PAD_LEFT);
    }

    public static function countBySexe(?int $arrondissementId = null, ?int $annee = null): array
    {
        $where  = ['statut = ?'];
        $values = ['ACTIF'];

        if ($arrondissementId !== null) {
            $where[]  = 'arrondissement_id = ?';
            $values[] = $arrondissementId;
        }
        if ($annee !== null) {
            $where[]  = 'annee = ?';
            $values[] = $annee;
        }

        $stmt = self::db()->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(defunt_sexe = 'M') AS masculin,
                    SUM(defunt_sexe = 'F') AS feminin
             FROM deces WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($values);
        return $stmt->fetch() ?: ['total' => 0, 'masculin' => 0, 'feminin' => 0];
    }
}
