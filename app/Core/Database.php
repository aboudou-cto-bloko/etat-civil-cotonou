<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Supporte à la fois nos variables et celles du plugin Railway MySQL
            $host    = $_ENV['MYSQL_ADDON_HOST']     ?? $_ENV['DB_HOST']     ?? $_ENV['MYSQLHOST']     ?? '127.0.0.1';
            $port    = $_ENV['MYSQL_ADDON_PORT']     ?? $_ENV['DB_PORT']     ?? $_ENV['MYSQLPORT']     ?? '3306';
            $db      = $_ENV['MYSQL_ADDON_DB']       ?? $_ENV['DB_DATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? '';
            $user    = $_ENV['MYSQL_ADDON_USER']     ?? $_ENV['DB_USERNAME'] ?? $_ENV['MYSQLUSER']     ?? '';
            $pass    = $_ENV['MYSQL_ADDON_PASSWORD'] ?? $_ENV['DB_PASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new PDOException("Connexion BDD impossible : " . $e->getMessage(), (int) $e->getCode());
            }
        }

        return self::$connection;
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollback(): bool
    {
        return self::getConnection()->rollBack();
    }
}
