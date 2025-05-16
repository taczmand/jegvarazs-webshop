<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Detect Laravel root
$laravelPath = is_dir(__DIR__.'/../laravel') ? __DIR__.'/../laravel' : __DIR__.'/..';

// Maintenance mode check
if (file_exists($maintenance = $laravelPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader
require $laravelPath.'/vendor/autoload.php';

// Bootstrap app
$app = require_once $laravelPath.'/bootstrap/app.php';

// Handle the request
$app->handleRequest(Request::capture());
