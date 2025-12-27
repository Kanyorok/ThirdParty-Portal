<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\LoginThirdPartyRequest;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ThirdPartyAuthController extends Controller
{
    public function login(LoginThirdPartyRequest $request): JsonResponse
    {
        $user = ThirdPartyUser::with([
            'thirdParty.types',
            'thirdParty.status',
            'thirdParty.supplierMaster'
        ])
            ->where('Email', strtolower($request->email))
            ->first();

        if (!$user || !Hash::check($request->password, $user->Password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unverified'),
                'requires_verification' => true
            ], 403);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.inactive')
            ], 403);
        }

        $token = $user->createToken('auth-token', ['third_party'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('auth.login_success'),
            'user'    => new ThirdPartyUserResource($user),
            'token'   => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'thirdParty.types',
            'thirdParty.status',
            'thirdParty.supplierMaster',
            'thirdParty.supplierMaster.status'
        ]);

        return response()->json([
            'success' => true,
            'user' => new ThirdPartyUserResource($user)
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => __('auth.logout_success')
        ]);
    }
}
