<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Ramsey\Uuid\Uuid;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::getConnection();
    }

    protected static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public static function find(string|int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findBy(array $conditions): ?array
    {
        [$where, $values] = self::buildWhere($conditions);
        $stmt = self::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE ' . $where . ' LIMIT 1');
        $stmt->execute($values);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function all(array $conditions = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        $values = [];

        if (!empty($conditions)) {
            [$where, $values] = self::buildWhere($conditions);
            $sql .= ' WHERE ' . $where;
        }

        if ($orderBy) {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        if ($offset > 0) {
            $sql .= ' OFFSET ' . $offset;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    }

    public static function count(array $conditions = []): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . static::$table;
        $values = [];

        if (!empty($conditions)) {
            [$where, $values] = self::buildWhere($conditions);
            $sql .= ' WHERE ' . $where;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($values);
        return (int) $stmt->fetchColumn();
    }

    public static function insert(array $data): string
    {
        if (!isset($data[static::$primaryKey])) {
            $data[static::$primaryKey] = self::generateUuid();
        }

        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = self::db()->prepare(
            'INSERT INTO ' . static::$table . ' (' . $columns . ') VALUES (' . $placeholders . ')'
        );
        $stmt->execute(array_values($data));

        return (string) $data[static::$primaryKey];
    }

    public static function update(string|int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $stmt = self::db()->prepare(
            'UPDATE ' . static::$table . ' SET ' . $set . ' WHERE ' . static::$primaryKey . ' = ?'
        );

        return $stmt->execute([...array_values($data), $id]);
    }

    public static function delete(string|int $id): bool
    {
        $stmt = self::db()->prepare(
            'DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?'
        );
        return $stmt->execute([$id]);
    }

    public static function paginate(array $conditions = [], int $perPage = 20, int $page = 1, string $orderBy = 'created_at DESC'): array
    {
        $total  = self::count($conditions);
        $offset = ($page - 1) * $perPage;
        $items  = self::all($conditions, $orderBy, $perPage, $offset);

        return [
            'data'         => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    private static function buildWhere(array $conditions): array
    {
        $clauses = [];
        $values  = [];

        foreach ($conditions as $col => $val) {
            if ($val === null) {
                $clauses[] = "{$col} IS NULL";
            } else {
                $clauses[] = "{$col} = ?";
                $values[]  = $val;
            }
        }

        return [implode(' AND ', $clauses), $values];
    }
}
