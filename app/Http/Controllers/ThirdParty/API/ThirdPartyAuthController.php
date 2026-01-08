<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\ThirdParty\SupplierMaster;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\Insurance\BancassuranceCustomer;
use App\Http\Requests\ThirdParty\Api\LoginThirdPartyRequest;
use App\Http\Requests\ThirdParty\Api\NewThirdPartyRequest;
use App\Http\Resources\ThirdParty\Api\ThirdPartyUserResource;
use App\Services\RegistrationService;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\ThirdParty\ResetPasswordRequest;

class ThirdPartyAuthController extends Controller
{
    protected RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function register(NewThirdPartyRequest $request): JsonResponse
    {
        try {
            $userData = $this->registrationService->registerThirdParty($request->validated());

            return response()->json([
                'success' => true,
                'message' => __('auth.registration_personal_successful'),
                'userId' => $userData->UserID,
                'redirectUrl' => '/register/third-party-details?user_id=' . $userData->UserID,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Third-party registration failed', [
                'error' => $e->getMessage(),
                'payload' => $request->except(['user_Password', 'user_Password_confirmation']),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('auth.registration_failed'),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function login(LoginThirdPartyRequest $request): JsonResponse
    {
        $user = ThirdPartyUser::with([
            'thirdParty.types',
            'thirdParty.supplierMaster',
            'thirdParty.tenantProfile',
            'thirdParty.customerProfile'
        ])
            ->where('Email', strtolower($request->email))
            ->first();

        if (!$user || !Hash::check($request->password, $user->Password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.invalid_credentials')],
            ]);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.account_inactive')
            ], 403);
        }

        if (!$user->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.acc_not_approved')
            ], 403);
        }

        $profileType = $request->input('profile_type');
        if ($profileType) {
            $isAuthorized = match ($profileType) {
                'Supplier' => SupplierMaster::where('ThirdPartyId', $user->ThirdPartyId)
                    ->where('ApprovalStatus', ThirdPartyApprovalStatusEnum::Approved->value)
                    ->exists(),
                'Tenant' => PropertyNewTenant::where('ThirdPartyId', $user->ThirdPartyId)
                    ->where('IsActive', true)
                    ->exists(),
                'Customer' => BancassuranceCustomer::where('ThirdPartyId', $user->ThirdPartyId)
                    ->exists(),
                default => false,
            };

            if (!$isAuthorized) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not authorized for the selected profile type.'
                ], 403);
            }
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth-token', ['third_party'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('auth.login_successful'),
            'user' => new ThirdPartyUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user->load([
            'thirdParty.types',
            'thirdParty.supplierMaster',
            'thirdParty.tenantProfile',
            'thirdParty.customerProfile'
        ]);

        return response()->json([
            'success' => true,
            'user' => new ThirdPartyUserResource($user)
        ]);
    }

    public function validateToken(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->isActive() || !$user->isApproved()) {
            return response()->json(['valid' => false], 403);
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $user->getAuthIdentifier(),
                'email' => $user->Email,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => __('auth.logout_successful')
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('thirdparties')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['success' => true, 'message' => __($status)])
            : response()->json(['success' => false, 'message' => __($status)], 400);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::broker('thirdparties')->reset(
            $request->validated(),

            function ($user, $password) {
                $user->forceFill([
                    'Password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['success' => true, 'message' => __($status)])
            : response()->json(['success' => false, 'message' => __($status)], 400);
    }
}
