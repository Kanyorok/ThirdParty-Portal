<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleActiveSession
{
    /**
     * Ensure a user has only one active database session at a time.
     * If another session is detected, keep the current one and delete others.
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

        if (config('session.driver') !== 'database') {
            return $next($request);
        }

        $user = Auth::user();
        $userId = $user->getAuthIdentifier();
        $currentSessionId = $request->session()->getId();
        $connection = config('session.connection');
        $table = config('session.table', 'sessions');

        try {
            // Cache-based single-session token enforcement (works even without DB columns)
            $cacheKey = 'user_session_token_' . $userId;
            $cacheToken = Cache::get($cacheKey);
            $sessionToken = (string)$request->session()->get('session_token', '');

            if ($cacheToken) {
                if (empty($sessionToken) || !hash_equals((string)$cacheToken, (string)$sessionToken)) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    $loginUrl = route('login');
                    if ($request->headers->has('HX-Request')) {
                        return redirect()->to($loginUrl)->withHeaders(['HX-Redirect' => $loginUrl]);
                    }
                    return redirect()->guest($loginUrl);
                }
            } elseif (!empty($sessionToken)) {
                // Seed cache if missing
                Cache::put($cacheKey, $sessionToken, now()->addMinutes(((int)config('session.lifetime', 20)) + 5));
            }

            // If the user's current_session_id is set and doesn't match this one, kill this session immediately
            if (!empty($user->current_session_id) && $user->current_session_id !== $currentSessionId) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $loginUrl = route('login');
                if ($request->headers->has('HX-Request')) {
                    return redirect()->to($loginUrl)->withHeaders(['HX-Redirect' => $loginUrl]);
                }
                return redirect()->guest($loginUrl);
            }

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


