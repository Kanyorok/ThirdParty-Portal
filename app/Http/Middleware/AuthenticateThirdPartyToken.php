<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateThirdPartyToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated - No token provided',
            ], 401);
        }

        // Parse token
        if (! str_contains($token, '|')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token format',
            ], 401);
        }

        [$id, $tokenValue] = explode('|', $token, 2);

        // Get the correct PersonalAccessToken model
        $modelClass = Sanctum::$personalAccessTokenModel ?? \App\Models\Auth\PersonalAccessToken::class;

        // Find the token
        $accessToken = $modelClass::find($id);

        if (! $accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found',
            ], 401);
        }

        // Verify the token hash
        if (! hash_equals($accessToken->token, hash('sha256', $tokenValue))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
            ], 401);
        }

        // Check if token is expired
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired',
            ], 401);
        }

        // Get the user
        $user = $accessToken->tokenable;

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 401);
        }

        // Set the authenticated user
        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        // Store the access token for logout
        $request->attributes->set('sanctum_token', $accessToken);

        return $next($request);
    }
}
