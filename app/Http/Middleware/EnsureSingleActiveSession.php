<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleActiveSession
{
    /**
     * Ensure a user has only one active database session at a time.
     * If another session is detected, keep the current one and delete others.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        if (config('session.driver') !== 'database') {
            return $next($request);
        }

        $userId = Auth::id();
        $currentSessionId = $request->session()->getId();
        $connection = config('session.connection');
        $table = config('session.table', 'sessions');

        try {
            // Delete all other sessions for this user
            DB::connection($connection)
                ->table($table)
                ->where('user_id', '=', $userId)
                ->where('id', '!=', $currentSessionId)
                ->delete();
        } catch (\Throwable $e) {
            // Swallow errors to avoid impacting UX; logging could be added if desired
        }

        return $next($request);
    }
}


