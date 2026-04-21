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

$request = new Request();
$router  = new Router($request);

require_once BASE_PATH . '/config/routes.php';

$router->resolve();
