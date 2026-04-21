<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT u.*, r.code AS role_code, a.nom AS arrondissement_nom
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN arrondissements a ON u.arrondissement_id = a.id
             WHERE u.email = ? AND u.deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function authenticate(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        if (!$user['is_active']) {
            return null;
        }
        return $user;
    }

    public static function allWithRole(array $conditions = []): array
    {
        $where  = '';
        $values = [];

        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $col => $val) {
                $clauses[] = "u.{$col} = ?";
                $values[]  = $val;
            }
            $where = 'WHERE ' . implode(' AND ', $clauses) . ' AND u.deleted_at IS NULL';
        } else {
            $where = 'WHERE u.deleted_at IS NULL';
        }

        $stmt = self::db()->prepare(
            "SELECT u.id, u.matricule, u.nom, u.prenom, u.email, u.telephone,
                    u.is_active, u.last_login_at, u.created_at,
                    r.code AS role_code, r.libelle AS role_libelle,
                    a.nom AS arrondissement_nom, a.numero AS arrondissement_numero
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN arrondissements a ON u.arrondissement_id = a.id
             {$where}
             ORDER BY a.numero ASC, u.nom ASC"
        );
        $stmt->execute($values);
        return $stmt->fetchAll();
    }

    public static function updateLastLogin(string $id): void
    {
        self::db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')
                  ->execute([$id]);
    }

    public static function create(array $data): string
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        unset($data['password']);
        return self::insert($data);
    }

    public static function changePassword(string $id, string $newPassword): bool
    {
        return self::update($id, [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
    }
}
