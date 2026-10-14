<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'event.status' => \App\Http\Middleware\CheckEventStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

if (env('APP_STORAGE')) {
    $storagePath = env('APP_STORAGE');
    if (!is_dir($storagePath.'/framework/views')) {
        @mkdir($storagePath.'/app', 0777, true);
        @mkdir($storagePath.'/framework/cache/data', 0777, true);
        @mkdir($storagePath.'/framework/views', 0777, true);
        @mkdir($storagePath.'/framework/sessions', 0777, true);
        @mkdir($storagePath.'/logs', 0777, true);
    }
    $app->useStoragePath($storagePath);
}

return $app;
