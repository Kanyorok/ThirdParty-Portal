<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\LoginThirdPartyRequest;
use App\Http\Requests\ThirdPartyAuth\RegisterThirdPartyUserRequest;
use App\Http\Resources\ThirdParty\Api\ThirdPartyUserResource;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ThirdPartyAuthController extends Controller
{
    protected RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function register(RegisterThirdPartyUserRequest $request): JsonResponse
    {
        try {
            $userData = $this->registrationService->registerThirdParty($request->validated());

            return response()->json([
                'success' => true,
                'message' => __('auth.registration_personal_successful'),
                'user' => [
                    'id' => $userData->UserID,
                    'userId' => $userData->UserID,
                ],
                'redirectUrl' => '/register/third-party-details?user_id=' . $userData->UserID,
            ], 201);
        } catch (\Exception $e) {
            // Force-write to single channel so it goes to storage/logs/laravel.log
            Log::channel('single')->error('Third-party registration failed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'ip' => $request->ip(),
                'forwarded_for' => $request->header('X-Forwarded-For'),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'route' => optional($request->route())->getName(),
                'payload' => $request->except(['Password', 'Password_confirmation']),
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

        try {
            $user = ThirdPartyUser::where('Email', $request->email)->first();


            if (! $user || ! Hash::check($request->password, $user->Password)) {
                throw ValidationException::withMessages([
                    'email' => __('auth.invalid_credentials'),
                ]);
            }

            // Enforce account status BEFORE creating token
            if (! $user->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.account_inactive'),
                ], 403);
            }
            if (! $user->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.acc_not_approved'),
                ], 403);
            }


            // Optional: single-session behavior
            $user->tokens()->delete();

            $token = $user->createToken('api')->plainTextToken;

            $responseData = [
                'user' => (new ThirdPartyUserResource($user->load(['thirdParty.types'])))->resolve(),
                'token' => $token,
                'token_type' => 'Bearer',
            ];



            return response()->json($responseData);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login Exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => __('auth.login_failed'),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // Token validation for SPA
    public function validateToken(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['valid' => false], 401);
        }
        $isActive = $user->isActive();
        $isApproved = $user->isApproved();
        if (! $isActive || ! $isApproved) {
            return response()->json(['valid' => false], 403);
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $user->getAuthIdentifier(),
                'email' => $user->Email,
                'isActive' => $isActive,
                'isApproved' => $isApproved,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            if (Auth::guard('sanctum')->check()) {
                $request->user()->currentAccessToken()->delete();

                return response()->json(['message' => __('auth.logout_successful')]);
            }

            return response()->json(['message' => __('auth.not_authenticated')], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('auth.logout_failed'),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function verifyEmail(string $id, string $hash): JsonResponse
    {
        $user = $this->resolveThirdPartyUser($id);

        if (! $user || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => __('auth.invalid_verification_link'),
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => __('auth.email_already_verified'),
                'user' => [
                    'id' => $user->UserID,
                ],
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => __('auth.email_verified'),
            'user' => [
                'id' => $user->UserID,
            ],
        ], 200);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $this->resolveThirdPartyUser($request->user_id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.unauthenticated'),
            ], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.email_already_verified'),
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => __('auth.verification_link_sent'),
        ], 200);
    }

    private function resolveThirdPartyUser(?string $id = null): ?ThirdPartyUser
    {
        // 1. Try to get user from request if already authenticated (e.g., resend request)
        $user = request()->user();

        if ($user instanceof ThirdPartyUser) {
            return $user;
        }

        if (! $id) {
            return null;
        }

        // 2. Try numeric database ID first (used in signed verification links)
        if (is_numeric($id)) {
            $userByPk = ThirdPartyUser::find($id);
            if ($userByPk) {
                return $userByPk;
            }
        }

        // 3. Fallback to string UserID (the 8-char random ID used in some frontend flows)
        return ThirdPartyUser::where('UserID', $id)->first();
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // We use the 'thirdparties' broker defined in config/auth.php
        $status = Password::broker('thirdparties')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400); // translation strings from resources/lang
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::broker('thirdparties')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // Handle custom column 'Password' and hashing
                $user->forceFill([
                    'Password' => Hash::make($password),
                ])->save();

                // Clear tokens if api setup requires it, though createsToken() handles login separately
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
}
