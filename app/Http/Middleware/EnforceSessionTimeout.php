<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionTimeout
{
    /**
     * Enforce server-side idle timeout for database sessions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip static asset requests like favicon
        if ($request->is('favicon.ico')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return $next($request);
        }

        // Skip for non-database drivers
        if (config('session.driver') !== 'database') {
            return $next($request);
        }

        // Avoid looping on the timeout endpoint itself
        if ($request->routeIs('timeout')) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $connection = config('session.connection');
        $table = config('session.table', 'sessions');
        $lifetimeSeconds = (int) config('session.lifetime', 20) * 60;

        try {
            $row = DB::connection($connection)->table($table)->where('id', $sessionId)->first();
        } catch (\Throwable $e) {
            // If we cannot read the session row, let the request proceed rather than breaking the app
            return $next($request);
        }

        if ($row && isset($row->last_activity)) {
            $idleSeconds = time() - (int) $row->last_activity;
            if ($idleSeconds > $lifetimeSeconds) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // HTMX-friendly redirect header
                $loginUrl = route('login');
                if ($request->headers->has('HX-Request')) {
                    return redirect()->to($loginUrl)->with('status', 'Session expired due to inactivity. Please log in again.')
                        ->withHeaders(['HX-Redirect' => $loginUrl]);
                }

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Session expired due to inactivity. Please log in again.'], 401)
                        ->withHeaders(['HX-Redirect' => $loginUrl]);
                }

                return redirect()->route('login')->with('status', 'Session expired due to inactivity. Please log in again.');
            }
        }

        return $next($request);
    }
}


