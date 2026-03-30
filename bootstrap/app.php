<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'hidden.auth' => \App\Http\Middleware\HiddenAuth::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/chat/pusher/auth',
            'chat/pusher/auth'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();