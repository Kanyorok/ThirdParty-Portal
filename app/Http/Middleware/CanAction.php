<?php

namespace App\Http\Middleware;

use App\Services\Core\PermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanAction
{
    /**
     * Enforce submodule action permission via PermissionResolver.
     * Usage: canAction:action,submodule OR canAction:submodule,action (order-agnostic)
     */
    public function handle(Request $request, Closure $next, string $param1, string $param2 = null)
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        // Super-admin bypass
        try {
            if ($user instanceof \App\Models\Auth\User && $user->hasRole(['admin', 'Admin', 'super-admin', 'Super Admin'])) {
                return $next($request);
            }
        } catch (\Throwable $e) {
        }

        [$a, $b] = [$param1, (string)$param2];
        $a = strtolower(trim($a));
        $b = strtolower(trim($b));

        // Determine which is action vs submodule; known actions list
        $actions = ['read','view','write','create','update','edit','delete','destroy','approve','approval'];
        $action = in_array($a, $actions, true) ? $a : (in_array($b, $actions, true) ? $b : '');
        $submodule = $action === $a ? $b : $a;

        if ($action === '' || $submodule === '') {
            // Misconfigured usage; deny by default
            abort(403);
        }

        // Normalize action synonyms handled by resolver
        $allowed = PermissionResolver::can($user, $submodule, $action);
        if (!$allowed) {
            abort(403);
        }

        return $next($request);
    }
}
