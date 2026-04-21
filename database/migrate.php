<?php

/**
 * Script d'initialisation de la base de données.
 * Usage : php database/migrate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$db   = $_ENV['DB_DATABASE'] ?? 'etat_civil_cotonou';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Connexion MySQL établie.\n";

    $schema = file_get_contents(__DIR__ . '/schema.sql');
    foreach (explode(';', $schema) as $statement) {
        $stmt = trim($statement);
        if ($stmt) {
            $pdo->exec($stmt);
        }
    }
    echo "Schéma créé avec succès.\n";

    $seeds = file_get_contents(__DIR__ . '/seeds.sql');
    foreach (explode(';', $seeds) as $statement) {
        $stmt = trim($statement);
        if ($stmt) {
            $pdo->exec($stmt);
        }
    }
    echo "Données initiales insérées.\n";
    echo "\nBase de données prête. Compte admin : admin@etatcivil-cotonou.bj\n";
    echo "IMPORTANT : Changez le mot de passe administrateur immédiatement.\n";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
