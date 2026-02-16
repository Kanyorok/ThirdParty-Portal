<?php

namespace App\Http\Middleware;

use Closure;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission, ...$guards)
    {
        $class = 'Spatie\\Permission\\Middlewares\\RoleOrPermissionMiddleware';
        if (class_exists($class)) {
            $inner = app($class);

            return $inner->handle($request, $next, $roleOrPermission, ...$guards);
        }

        return $next($request);
    }
}
