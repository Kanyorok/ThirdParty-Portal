<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Enums\ThirdPartyApprovalStatusEnum;

class CheckThirdPartyApprovalStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user instanceof ThirdPartyUser) {
            return response()->json([
                'message' => __('auth.unauthenticated')
            ], 401);
        }

        if (!$user->thirdParty || $user->thirdParty->ApprovalStatus !== ThirdPartyApprovalStatusEnum::Approved) {
            return response()->json([
                'message' => __('auth.acc_not_approved')
            ], 403);
        }

        return $next($request);
    }
}
