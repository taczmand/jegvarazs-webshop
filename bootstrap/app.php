<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\CustomerAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin.auth' => AdminAuth::class,
            'customer.auth' => CustomerAuth::class,
        ]);
        // CSRF kivétel az adott route-okhoz
        $middleware->validateCsrfTokens(except: [
            '/payment/simplepay/callback', // SimplePay callback
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
