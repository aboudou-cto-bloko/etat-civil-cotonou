<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Mariage extends Model
{
    protected static string $table = 'mariages';

    public static function search(array $filters, ?int $arrondissementId = null, int $perPage = 20, int $page = 1, string $sort = 'created_at', string $direction = 'desc'): array
    {
        $allowedSorts = ['epoux_nom', 'date_mariage', 'numero_acte', 'created_at'];
        $sort      = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $direction = $direction === 'asc' ? 'ASC' : 'DESC';
        $where  = ['m.statut != ?'];
        $values = ['ANNULÉ'];

        if ($arrondissementId !== null) {
            $where[]  = 'm.arrondissement_id = ?';
            $values[] = $arrondissementId;
        }

        if (!empty($filters['nom'])) {
            $where[]  = "(m.epoux_nom LIKE ? OR m.epoux_prenom LIKE ? OR m.epouse_nom LIKE ? OR m.epouse_prenom LIKE ?)";
            $like     = '%' . $filters['nom'] . '%';
            $values   = array_merge($values, [$like, $like, $like, $like]);
        }

        if (!empty($filters['numero_acte'])) {
            $where[]  = 'm.numero_acte = ?';
            $values[] = $filters['numero_acte'];
        }

        if (!empty($filters['annee'])) {
            $where[]  = 'm.annee = ?';
            $values[] = (int) $filters['annee'];
        }

        if (!empty($filters['type_mariage'])) {
            $where[]  = 'm.type_mariage = ?';
            $values[] = strtoupper($filters['type_mariage']);
        }

        $whereStr = implode(' AND ', $where);

        $countStmt = self::db()->prepare("SELECT COUNT(*) FROM mariages m WHERE {$whereStr}");
        $countStmt->execute($values);
        $total  = (int) $countStmt->fetchColumn();
        $offset = ($page - 1) * $perPage;

        $stmt = self::db()->prepare(
            "SELECT m.*, a.nom AS arrondissement_nom, a.numero AS arrondissement_numero,
                    u.nom AS enregistre_par_nom, u.prenom AS enregistre_par_prenom
             FROM mariages m
             JOIN arrondissements a ON m.arrondissement_id = a.id
             JOIN users u ON m.enregistre_par = u.id
             WHERE {$whereStr}
             ORDER BY m.{$sort} {$direction}
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
            "SELECT m.*, a.nom AS arrondissement_nom, a.numero AS arrondissement_numero,
                    u.nom AS enregistre_par_nom, u.prenom AS enregistre_par_prenom,
                    oe.nom AS officier_nom, oe.prenom AS officier_prenom
             FROM mariages m
             JOIN arrondissements a ON m.arrondissement_id = a.id
             JOIN users u ON m.enregistre_par = u.id
             JOIN users oe ON m.officier_etat_civil_id = oe.id
             WHERE m.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $naissance = $stmt->fetch();
        if (!$naissance) {
            return null;
        }

        // Épouses supplémentaires si polygamie
        $stmt2 = self::db()->prepare(
            "SELECT * FROM mariage_epouses_supplementaires WHERE mariage_id = ? ORDER BY ordre_epouse ASC"
        );
        $stmt2->execute([$id]);
        $naissance['epouses_supplementaires'] = $stmt2->fetchAll();

        return $naissance;
    }

    public static function prochainNumeroActe(int $arrondissementId, int $annee): string
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) + 1 FROM mariages WHERE arrondissement_id = ? AND annee = ?"
        );
        $stmt->execute([$arrondissementId, $annee]);
        return str_pad((string) ((int) $stmt->fetchColumn()), 4, '0', STR_PAD_LEFT);
    }

    public static function countByType(?int $arrondissementId = null, ?int $annee = null): array
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
                    SUM(type_mariage = 'MONOGAMIQUE') AS monogamiques,
                    SUM(type_mariage = 'POLYGAMIQUE') AS polygamiques
             FROM mariages WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($values);
        return $stmt->fetch() ?: ['total' => 0, 'monogamiques' => 0, 'polygamiques' => 0];
    }
}
