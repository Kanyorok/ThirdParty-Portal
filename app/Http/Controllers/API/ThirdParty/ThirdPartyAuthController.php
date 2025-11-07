<?php

namespace App\Http\Controllers\API\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Http\Requests\ThirdPartyAuth\RegisterThirdPartyUserRequest;
use App\Http\Requests\ThirdPartyAuth\LoginThirdPartyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;

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
            $userData = $this->registrationService->registerUser($request->validated());

            return response()->json([
                'message' => __('auth.registration_personal_successful'),
                'userId' => $userData->UserID,
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
                    'email' => __('auth.invalid_credentials')
                ]);
            }

            // Enforce account status BEFORE creating token
            if (!$user->isActive()) {
                return response()->json(['message' => __('auth.account_inactive')], 403);
            }
            if (! $user->isApproved()) {
                return response()->json(['message' => __('auth.acc_not_approved')], 403);
            }

            // Optional: single-session behavior
            $user->tokens()->delete();

            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'user' => new ThirdPartyUserResource($user->load(['thirdParty.types'])),
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
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
        if (!$user) {
            return response()->json(['valid' => false], 401);
        }
        $isActive = $user->isActive();
        $isApproved = $user->isApproved();
        if (!$isActive || !$isApproved) {
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
            return response()->json(['message' => __('auth.invalid_verification_link')], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('auth.email_already_verified')], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => __('auth.email_verified')], 200);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $this->resolveThirdPartyUser($request->user_id);

        if (! $user) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('auth.email_already_verified')], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => __('auth.verification_link_sent')], 200);
    }

    private function resolveThirdPartyUser(?string $id = null): ?ThirdPartyUser
    {
        return Auth::guard('sanctum')->user() ?? ($id ? ThirdPartyUser::where('UserID', $id)->first() : null);
    }
}
