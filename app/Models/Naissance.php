<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Naissance extends Model
{
    protected static string $table = 'naissances';

    public static function search(array $filters, ?int $arrondissementId = null, int $perPage = 20, int $page = 1, string $sort = 'created_at', string $direction = 'desc'): array
    {
        $allowedSorts = ['enfant_nom', 'date_naissance', 'numero_acte', 'created_at'];
        $sort      = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $direction = $direction === 'asc' ? 'ASC' : 'DESC';

        $where  = ['n.statut != ?'];
        $values = ['ANNULÉ'];

        if ($arrondissementId !== null) {
            $where[]  = 'n.arrondissement_id = ?';
            $values[] = $arrondissementId;
        }

        if (!empty($filters['nom'])) {
            $where[]  = "(n.enfant_nom LIKE ? OR n.enfant_prenom LIKE ?)";
            $values[] = '%' . $filters['nom'] . '%';
            $values[] = '%' . $filters['nom'] . '%';
        }

        if (!empty($filters['numero_acte'])) {
            $where[]  = 'n.numero_acte = ?';
            $values[] = $filters['numero_acte'];
        }

        if (!empty($filters['annee'])) {
            $where[]  = 'n.annee = ?';
            $values[] = (int) $filters['annee'];
        }

        if (!empty($filters['date_debut'])) {
            $where[]  = 'DATE(n.date_naissance) >= ?';
            $values[] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $where[]  = 'DATE(n.date_naissance) <= ?';
            $values[] = $filters['date_fin'];
        }

        $whereStr = implode(' AND ', $where);

        $countStmt = self::db()->prepare("SELECT COUNT(*) FROM naissances n WHERE {$whereStr}");
        $countStmt->execute($values);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $stmt = self::db()->prepare(
            "SELECT n.*, a.nom AS arrondissement_nom, a.numero AS arrondissement_numero,
                    u.nom AS enregistre_par_nom, u.prenom AS enregistre_par_prenom
             FROM naissances n
             JOIN arrondissements a ON n.arrondissement_id = a.id
             JOIN users u ON n.enregistre_par = u.id
             WHERE {$whereStr}
             ORDER BY n.{$sort} {$direction}
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
            "SELECT n.*, a.nom AS arrondissement_nom, a.numero AS arrondissement_numero,
                    u.nom AS enregistre_par_nom, u.prenom AS enregistre_par_prenom,
                    oe.nom AS officier_nom, oe.prenom AS officier_prenom
             FROM naissances n
             JOIN arrondissements a ON n.arrondissement_id = a.id
             JOIN users u ON n.enregistre_par = u.id
             JOIN users oe ON n.officier_etat_civil_id = oe.id
             WHERE n.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function prochainNumeroActe(int $arrondissementId, int $annee): string
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) + 1 FROM naissances
             WHERE arrondissement_id = ? AND annee = ?"
        );
        $stmt->execute([$arrondissementId, $annee]);
        $seq = (int) $stmt->fetchColumn();
        return str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public static function countByArrondissement(?int $arrondissementId = null, ?int $annee = null): array
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

        $whereStr = implode(' AND ', $where);

        $stmt = self::db()->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(enfant_sexe = 'M') AS masculin,
                SUM(enfant_sexe = 'F') AS feminin,
                SUM(enfant_est_jumeau = 1) AS jumeaux
             FROM naissances
             WHERE {$whereStr}"
        );
        $stmt->execute($values);
        return $stmt->fetch() ?: ['total' => 0, 'masculin' => 0, 'feminin' => 0, 'jumeaux' => 0];
    }
}
