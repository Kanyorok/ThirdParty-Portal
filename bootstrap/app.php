<?php

use App\Http\Middleware\TransformApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->group(base_path('routes/portal.php'));

            Route::middleware('web')
                ->prefix('procurement')
                ->group(base_path('routes/procurement.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases for route middleware
        $middleware->alias([
            'ajax' => \App\Http\Middleware\AjaxCheckMiddleware::class,
            'license' => \App\Http\Middleware\RequireLicense::class,
            'module' => \App\Http\Middleware\RequireModule::class,
            'canAction' => \App\Http\Middleware\CanAction::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'auth.thirdparty' => \App\Http\Middleware\AuthenticateThirdPartyToken::class,
        ]);

        $middleware->trimStrings(except: [
            'reports/*',
        ]);

        // Ensure CORS middleware is prepended to API group
        $middleware->prependToGroup('api', \Illuminate\Http\Middleware\HandleCors::class);

        // Transform keys of requests that are not GET to snake_case
        // and keys of successful JSON responses to camelCase
        $middleware->appendToGroup('api', [
            //     TransformApiRequest::class,
            TransformApiResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Add CORS headers to exception responses
        $exceptions->respond(function ($response) {
            if (method_exists($response, 'header')) {
                $response->header('Access-Control-Allow-Origin', request()->header('Origin') ?? '*');
                $response->header('Access-Control-Allow-Credentials', 'true');
            }
            return $response;
        });
    })->withEvents(discover: [
        __DIR__ . '/../app/Listeners',
    ])->create();
