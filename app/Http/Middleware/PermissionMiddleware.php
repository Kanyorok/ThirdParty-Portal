<?php

namespace App\Http\Middleware;

use Closure;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission, ...$guards)
    {
        $class = 'Spatie\\Permission\\Middlewares\\PermissionMiddleware';
        if (class_exists($class)) {
            $inner = app($class);
            return $inner->handle($request, $next, $permission, ...$guards);
        }
        return $next($request);
    }
}

