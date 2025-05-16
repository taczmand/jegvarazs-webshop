<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Meghatározzuk az elérési útvonalat a Laravel rendszerhez
$env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';

// Ha production, akkor a Laravel külön mappában van (pl. public_html mellett: ../laravel/)
$laravelBase = $env === 'local'
    ? __DIR__ . '/../'          // Lokális fejlesztés (minden egyben van)
    : __DIR__ . '/../laravel/'; // Éles környezet (külön mappában a Laravel core)

// Maintenance mód ellenőrzése
if (file_exists($maintenance = $laravelBase . 'storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoloader betöltése
require $laravelBase . 'vendor/autoload.php';

// Laravel bootstrappelése és a kérés kezelése
/** @var Application $app */
$app = require_once $laravelBase . 'bootstrap/app.php';

$app->handleRequest(Request::capture());
