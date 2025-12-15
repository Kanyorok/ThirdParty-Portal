<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\LoginThirdPartyRequest;
use App\Http\Requests\ThirdParty\RegisterThirdPartyUserRequest;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ThirdPartyAuthController extends Controller
{
    // minimal thirdparty registration
    public function register(RegisterThirdPartyUserRequest $request): JsonResponse
    {
        $user = ThirdPartyUser::create([
            'FirstName' => $request->validated('FirstName'),
            'LastName' => $request->validated('LastName'),
            'Email' => $request->validated('Email'),
            'Phone' => $request->validated('Phone'),
            'Password' => $request->validated('Password'),
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'data' => [
                'user_id' => $user->UserID,
                'email' => $user->Email,
            ],
        ], 201);
    }

    public function login(LoginThirdPartyRequest $request): JsonResponse
    {
        $user = ThirdPartyUser::where('Email', strtolower($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->Password)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.failed'),
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.email_not_verified'),
            ], 403);
        }

        $user->load(['thirdParty.thirdPartyTypes.codeDetail']);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('auth.login_success'),
            'user' => $this->transformUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user('third_party')->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user('third_party')->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user('third_party')->load('thirdParty');

        return response()->json([
            'data' => $this->transformUser($user),
        ]);
    }

    protected function transformUser(ThirdPartyUser $user): array
    {
        return [
            'user_id' => $user->UserID,
            'first_name' => $user->FirstName,
            'last_name' => $user->LastName,
            'full_name' => $user->FullName,
            'email' => $user->Email,
            'phone' => $user->Phone,
            'email_verified' => $user->hasVerifiedEmail(),
            'is_active' => $user->IsActive,
            'has_profile' => $user->hasProfile(),
            'is_approved' => $user->isApproved(),
            'profile' => $user->thirdParty ? [
                'name' => $user->thirdParty->ThirdPartyName,
                'trading_name' => $user->thirdParty->TradingName,
                'approval_status' => $user->thirdParty->ApprovalStatus?->value,
            ] : null,
        ];
    }
}
