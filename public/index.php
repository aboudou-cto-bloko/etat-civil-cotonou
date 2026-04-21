<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_START', microtime(true));

require_once BASE_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Router;
use App\Core\Request;

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Strict');
if (($_ENV['SESSION_SECURE'] ?? 'false') === 'true') {
    ini_set('session.cookie_secure', '1');
}

session_start();

// Security headers
$isProd = ($_ENV['APP_ENV'] ?? 'production') !== 'development';
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
if ($isProd) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self'; img-src 'self' data:; frame-ancestors 'none'");

$request = new Request();
$router  = new Router($request);

require_once BASE_PATH . '/config/routes.php';

$router->resolve();
