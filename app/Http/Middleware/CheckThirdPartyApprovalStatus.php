<?php

namespace App\Http\Middleware;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Models\ThirdParty\ThirdPartyUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckThirdPartyApprovalStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user instanceof ThirdPartyUser) {
            return response()->json([
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        if (! $user->thirdParty || $user->thirdParty->ApprovalStatus !== ThirdPartyApprovalStatusEnum::Approved) {
            return response()->json([
                'message' => __('auth.acc_not_approved'),
            ], 403);
        }

        return $next($request);
    }
}
