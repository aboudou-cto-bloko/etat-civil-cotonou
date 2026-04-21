<?php

/**
 * Script d'initialisation de la base de données.
 * Usage : php database/migrate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

function self_split_sql(string $sql): array
{
    // Supprime les commentaires sur une ligne (-- ...) pour éviter les faux splits sur ";"
    $sql  = preg_replace('/--[^\n]*/', '', $sql);
    $stmts = [];
    foreach (explode(';', $sql) as $part) {
        $part = trim($part);
        if ($part !== '') {
            $stmts[] = $part;
        }
    }
    return $stmts;
}

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$host = getenv('MYSQLHOST')     ?: getenv('DB_HOST')      ?: $_ENV['DB_HOST']     ?? '127.0.0.1';
$port = getenv('MYSQLPORT')     ?: getenv('DB_PORT')      ?: $_ENV['DB_PORT']     ?? '3306';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_DATABASE')  ?: $_ENV['DB_DATABASE'] ?? 'etat_civil_cotonou';
$user = getenv('MYSQLUSER')     ?: getenv('DB_USERNAME')  ?: $_ENV['DB_USERNAME'] ?? 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD')  ?: $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Connexion MySQL établie.\n";

    $schema = file_get_contents(__DIR__ . '/schema.sql');
    foreach (self_split_sql($schema) as $stmt) {
        $pdo->exec($stmt);
    }
    echo "Schéma créé avec succès.\n";

    $seeds = file_get_contents(__DIR__ . '/seeds.sql');
    foreach (self_split_sql($seeds) as $stmt) {
        $pdo->exec($stmt);
    }
    echo "Données initiales insérées.\n";
    echo "\nBase de données prête. Compte admin : admin@etatcivil-cotonou.bj\n";
    echo "IMPORTANT : Changez le mot de passe administrateur immédiatement.\n";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
