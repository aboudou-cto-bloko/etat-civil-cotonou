<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

class ArrondissementIsolationMiddleware
{
    // Rôles soumis à l'isolation — les admins ne passent pas par ce filtre
    private const RESTRICTED_ROLES = ['superviseur', 'analytics'];

    public function handle(Request $request, callable $next): void
    {
        $user = $_SESSION['user'] ?? null;

        if ($user && in_array($user['role_code'], self::RESTRICTED_ROLES, true)) {
            if (empty($user['arrondissement_id'])) {
                // Incohérence de données : un superviseur/analytics sans arrondissement
                session_destroy();
                header('Location: /login?error=missing_arrondissement');
                exit;
            }

            // Injecte la contrainte dans la requête pour que les modèles l'appliquent
            $request->setAttribute('arrondissement_id', (int) $user['arrondissement_id']);
        }

        // Les admins : arrondissement_id reste null → accès global

        $next();
    }
}
