<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ThirdPartyUser;

class EnforceThirdPartyUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!($user instanceof ThirdPartyUser)) {
            return response()->json([
                'message' => __('auth.access_denied')
            ], 403);
        }

        return $next($request);
    }
}
