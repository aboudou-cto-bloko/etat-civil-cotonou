<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\NaissanceController;
use App\Controllers\MariageController;
use App\Controllers\DecesController;
use App\Controllers\StatistiqueController;
use App\Controllers\UserController;
use App\Controllers\DocumentController;
use App\Controllers\SuggestionsController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\ArrondissementIsolationMiddleware;
use App\Middleware\RoleMiddleware;

// -------------------------------------------------------
// Routes publiques
// -------------------------------------------------------

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

// -------------------------------------------------------
// Routes protégées (auth + isolation arrondissement)
// -------------------------------------------------------

$router->group([
    'middleware' => [
        AuthMiddleware::class,
        ArrondissementIsolationMiddleware::class,
    ],
], function ($router) {

    // Tableau de bord
    $router->get('/', [DashboardController::class, 'index']);
    $router->get('/dashboard', [DashboardController::class, 'index']);

    // ---- Naissances (admin + superviseur) ----
    $router->get('/naissances', [NaissanceController::class, 'index'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->get('/naissances/nouveau', [NaissanceController::class, 'create'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->post('/naissances', [NaissanceController::class, 'store'],
        [new RoleMiddleware(['admin', 'superviseur']), CsrfMiddleware::class]);
    $router->get('/naissances/{id}', [NaissanceController::class, 'show'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->get('/naissances/{id}/modifier', [NaissanceController::class, 'edit'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->post('/naissances/{id}/modifier', [NaissanceController::class, 'update'],
        [new RoleMiddleware(['admin', 'superviseur']), CsrfMiddleware::class]);

    // ---- Mariages ----
    $router->get('/mariages', [MariageController::class, 'index'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->get('/mariages/nouveau', [MariageController::class, 'create'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->post('/mariages', [MariageController::class, 'store'],
        [new RoleMiddleware(['admin', 'superviseur']), CsrfMiddleware::class]);
    $router->get('/mariages/{id}', [MariageController::class, 'show'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->get('/mariages/{id}/modifier', [MariageController::class, 'edit'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->post('/mariages/{id}/modifier', [MariageController::class, 'update'],
        [new RoleMiddleware(['admin', 'superviseur']), CsrfMiddleware::class]);

    // ---- Décès ----
    $router->get('/deces', [DecesController::class, 'index'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->get('/deces/nouveau', [DecesController::class, 'create'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->post('/deces', [DecesController::class, 'store'],
        [new RoleMiddleware(['admin', 'superviseur']), CsrfMiddleware::class]);
    $router->get('/deces/{id}', [DecesController::class, 'show'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->get('/deces/{id}/modifier', [DecesController::class, 'edit'],
        [new RoleMiddleware(['admin', 'superviseur'])]);
    $router->post('/deces/{id}/modifier', [DecesController::class, 'update'],
        [new RoleMiddleware(['admin', 'superviseur']), CsrfMiddleware::class]);

    // ---- Génération PDF ----
    $router->get('/actes/{type}/{id}/pdf', [DocumentController::class, 'generate'],
        [new RoleMiddleware(['admin', 'superviseur'])]);

    // ---- Statistiques (tous les rôles) ----
    $router->get('/statistiques', [StatistiqueController::class, 'index']);
    $router->get('/statistiques/export', [StatistiqueController::class, 'export'],
        [new RoleMiddleware(['admin', 'superviseur', 'analytics'])]);

    // ---- Gestion utilisateurs (admin uniquement) ----
    $router->get('/utilisateurs', [UserController::class, 'index'],
        [new RoleMiddleware(['admin'])]);
    $router->get('/utilisateurs/nouveau', [UserController::class, 'create'],
        [new RoleMiddleware(['admin'])]);
    $router->post('/utilisateurs', [UserController::class, 'store'],
        [new RoleMiddleware(['admin']), CsrfMiddleware::class]);
    $router->get('/utilisateurs/{id}/modifier', [UserController::class, 'edit'],
        [new RoleMiddleware(['admin'])]);
    $router->post('/utilisateurs/{id}/modifier', [UserController::class, 'update'],
        [new RoleMiddleware(['admin']), CsrfMiddleware::class]);
    $router->post('/utilisateurs/{id}/desactiver', [UserController::class, 'deactivate'],
        [new RoleMiddleware(['admin']), CsrfMiddleware::class]);

    // Profil (tous)
    $router->get('/profil', [UserController::class, 'profile']);
    $router->post('/profil', [UserController::class, 'updateProfile'], [CsrfMiddleware::class]);

    // Suggestions autocomplete (JSON)
    $router->get('/api/suggestions', [SuggestionsController::class, 'search']);
});
