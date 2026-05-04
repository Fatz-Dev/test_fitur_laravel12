<?php

use App\Http\Middleware\EnsureRole;
use App\Services\JwtService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->encryptCookies(except: [JwtService::COOKIE_NAME]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
