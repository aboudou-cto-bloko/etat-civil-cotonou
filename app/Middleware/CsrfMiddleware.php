<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, callable $next): void
    {
        if (in_array($request->method(), self::SAFE_METHODS, true)) {
            $next();
            return;
        }

        // Génération du token à la première requête GET (dans View::csrfField())
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $requestToken = $request->csrfToken();

        if (!$sessionToken || !$requestToken || !hash_equals($sessionToken, $requestToken)) {
            http_response_code(419);
            echo 'Token CSRF invalide ou expiré. <a href="javascript:history.back()">Retour</a>';
            exit;
        }

        // Rotation du token après consommation
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $next();
    }
}
