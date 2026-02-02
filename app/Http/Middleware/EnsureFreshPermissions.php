<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureFreshPermissions
{
    /**
     * Handle an incoming request.
     *
     * Ensures that after permission:cache-reset is run, user sessions
     * are forced to reload permissions from the database instead of
     * using stale cached data.
     *
     * Note: Admin users bypass this via Gate::before() in AppServiceProvider,
     * so this primarily helps non-admin roles like ArchanaRole.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // All users get fresh permissions after cache clear
            // Admin role has all permissions in DB, no special bypass

            $cacheKey = config('permission.cache.key', 'spatie.permission.cache');

            // If the permission cache doesn't exist, it was recently cleared
            if (! Cache::has($cacheKey)) {
                // Force reload of permissions and roles from database
                if ($user) {
                    // Unset cached relationships to force fresh DB lookup
                    $user->unsetRelation('permissions');
                    $user->unsetRelation('roles');

                    // Force Spatie to rebuild its internal cache
                    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

                    // Forget any cached gate policy decisions
                    app(\Illuminate\Contracts\Auth\Access\Gate::class)->forgetPolicies();

                    // Log for debugging
                    Log::info('Forced permission reload for user', [
                        'user_id' => $user->UserID ?? $user->id,
                        'ip' => $request->ip(),
                    ]);
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if user has admin role that bypasses all permission checks
     *
     * @deprecated Admin role now uses standard permission system
     * @param mixed $user
     * @return bool
     */
    protected function isAdmin($user): bool
    {
        // Kept for reference but always returns false
        // Admin users now go through normal permission checks
        return false;
    }
}
