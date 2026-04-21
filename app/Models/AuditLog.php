<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';

    public static function log(
        string  $action,
        string  $typeEntite,
        ?string $entiteId      = null,
        ?array  $oldValues     = null,
        ?array  $newValues     = null
    ): void {
        $user = $_SESSION['user'] ?? null;

        self::insert([
            'user_id'           => $user['id'] ?? null,
            'arrondissement_id' => $user['arrondissement_id'] ?? null,
            'action'            => $action,
            'type_entite'       => $typeEntite,
            'entite_id'         => $entiteId,
            'anciennes_valeurs' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'nouvelles_valeurs' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'adresse_ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent'        => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    public static function recent(?int $arrondissementId = null, int $limit = 50): array
    {
        $where  = [];
        $values = [];

        if ($arrondissementId !== null) {
            $where[]  = 'l.arrondissement_id = ?';
            $values[] = $arrondissementId;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = self::db()->prepare(
            "SELECT l.*, u.nom AS user_nom, u.prenom AS user_prenom, r.code AS role_code
             FROM audit_logs l
             LEFT JOIN users u ON l.user_id = u.id
             LEFT JOIN roles r ON u.role_id = r.id
             {$whereStr}
             ORDER BY l.created_at DESC
             LIMIT {$limit}"
        );
        $stmt->execute($values);
        return $stmt->fetchAll();
    }

    public static function statsByUser(?int $arrondissementId = null, string $dateDebut = '', string $dateFin = ''): array
    {
        $where  = ["action IN ('CREATE','UPDATE','GENERATE_PDF')"];
        $values = [];

        if ($arrondissementId !== null) {
            $where[]  = 'l.arrondissement_id = ?';
            $values[] = $arrondissementId;
        }
        if ($dateDebut) {
            $where[]  = 'DATE(l.created_at) >= ?';
            $values[] = $dateDebut;
        }
        if ($dateFin) {
            $where[]  = 'DATE(l.created_at) <= ?';
            $values[] = $dateFin;
        }

        $stmt = self::db()->prepare(
            "SELECT u.nom, u.prenom, r.code AS role_code,
                    COUNT(*) AS total_actions,
                    SUM(l.action = 'CREATE') AS creations,
                    SUM(l.action = 'UPDATE') AS modifications,
                    SUM(l.action = 'GENERATE_PDF') AS pdf_generes
             FROM audit_logs l
             LEFT JOIN users u ON l.user_id = u.id
             LEFT JOIN roles r ON u.role_id = r.id
             WHERE " . implode(' AND ', $where) . "
             GROUP BY l.user_id
             ORDER BY total_actions DESC"
        );
        $stmt->execute($values);
        return $stmt->fetchAll();
    }
}
