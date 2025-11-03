<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $class = 'Spatie\\Permission\\Middlewares\\RoleMiddleware';
        if (class_exists($class)) {
            $inner = app($class);
            return $inner->handle($request, $next, ...$roles);
        }
        return $next($request);
    }
}

