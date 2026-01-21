<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifiedUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        \Illuminate\Support\Facades\Log::info('VerifiedUser Middleware Hit', ['user_id' => $user?->getAuthIdentifier()]);

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ThirdPartyUser model exposes helpers
        $isActive = method_exists($user, 'isActive') ? $user->isActive() : (bool)($user->IsActive ?? $user->isActive ?? false);
        $isApproved = method_exists($user, 'isApproved') ? $user->isApproved() : (bool)($user->isApproved ?? false);

        if (!$isActive) {
            \Illuminate\Support\Facades\Log::warning('VerifiedUser: Inactive', ['user_id' => $user->getAuthIdentifier()]);
            return response()->json(['error' => 'Account inactive'], 403);
        }

        if (!$isApproved) {
            \Illuminate\Support\Facades\Log::warning('VerifiedUser: Not Approved', ['user_id' => $user->getAuthIdentifier()]);
            return response()->json(['error' => 'Account not approved'], 403);
        }

        return $next($request);
    }
}
