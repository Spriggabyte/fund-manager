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
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report web request and queued job exceptions to Sentry. The SDK is a
        // no-op until SENTRY_LARAVEL_DSN is set, so this is safe in every env.
        \Sentry\Laravel\Integration::handles($exceptions);
    })->create();
