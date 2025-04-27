<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        \App\Console\Commands\CreateAdmin::class,
        \App\Console\Commands\AssignAdminRole::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware globaux - uniquement ceux nécessaires
        // $middleware->use([
        //     // Middlewares globaux si nécessaire
        // ]);
        
        // Définition minimale des middlewares pour Laravel 12
        $middleware->alias([
            'approved' => \App\Http\Middleware\EnsureUserIsApproved::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
