<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TransformApiRequest;
use App\Http\Middleware\TransformApiResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases for route middleware
        $middleware->alias([
            'ajax' => \App\Http\Middleware\AjaxCheckMiddleware::class,
            'license' => \App\Http\Middleware\RequireLicense::class,
            'module' => \App\Http\Middleware\RequireModule::class,
        ]);

        // Transform keys of requests that are not GET to snake_case
        // and keys of successful JSON responses to camelCase
        $middleware->appendToGroup('api', [
            //     TransformApiRequest::class,
            TransformApiResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->withEvents(discover: [
        __DIR__ . '/../app/Listeners',
    ])->create();
