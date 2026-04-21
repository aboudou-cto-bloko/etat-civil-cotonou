<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): void
    {
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        // Vérifier que le compte est actif
        if (!($_SESSION['user']['is_active'] ?? false)) {
            session_destroy();
            header('Location: /login?error=account_disabled');
            exit;
        }

        $next();
    }
}
