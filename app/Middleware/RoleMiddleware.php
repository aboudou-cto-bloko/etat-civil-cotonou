<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request, callable $next): void
    {
        $userRole = $_SESSION['user']['role_code'] ?? null;

        if (!in_array($userRole, $this->allowedRoles, true)) {
            http_response_code(403);
            include BASE_PATH . '/app/Views/errors/403.php';
            exit;
        }

        $next();
    }
}
