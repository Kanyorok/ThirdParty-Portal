<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Http\Requests\ThirdPartyAuth\RegisterThirdPartyUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;

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
            $token = $userData->createToken('auth-token', ['*'], now()->addDays(config('sanctum.expiration', 7)))->plainTextToken;

            return response()->json([
                'message' => __('auth.registration_successful'),
                'user' => new ThirdPartyUserResource($userData),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('auth.registration_failed'),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $user = ThirdPartyUser::where('Email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->Password)) {
                throw ValidationException::withMessages([
                    'email' => __('auth.invalid_credentials')
                ]);
            }

            if (! $user->isApproved()) {
                return response()->json([
                    'message' => __('auth.acc_not_approved')
                ], 403);
            }

            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'user' => new ThirdPartyUserResource($user),
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('auth.login_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
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
                'error' => $e->getMessage(),
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
            $user->IsActive = true;
            $user->save();
        }

        return response()->json(['message' => __('auth.email_verified')], 200);
    }

    public function resendVerification(): JsonResponse
    {
        $user = $this->resolveThirdPartyUser();

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
        return Auth::guard('sanctum')->user() ?? ($id ? ThirdPartyUser::find($id) : null);
    }
}
