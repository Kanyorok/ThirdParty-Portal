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
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ThirdPartyAuthController extends Controller
{
    protected RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function register(
        RegisterThirdPartyUserRequest $request
    ): JsonResponse {
        try {
            $userData = $this->registrationService->registerUser($request->validated());
            $token = $userData->createToken('auth-token', ['*'], now()->addDays(config('sanctum.expiration', 7)))->plainTextToken;

            return response()->json([
                'message' => 'Registration successful! Please check your email for verification.',
                'user' => new ThirdPartyUserResource($userData),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('auth.registration_failed_general'),
                'error' => config('app.debug') ? $e->getMessage() : null, // Good use of config('app.debug')
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $user = ThirdPartyUser::where('Email', $request->email)->first();

            // thirdpartyuser exists and pwd okay?
            if (! $user || ! Hash::check($request->password, $user->Password)) {
                throw ValidationException::withMessages(
                    [
                        'email' => __('auth.invalid_credentials')
                    ]
                );
            }
            // thirdparty associated with this user approved?
            if (! $user->isApproved()) {
                return response()->json(
                    [
                        'message' => __('auth.acc_not_approved')
                    ],
                    403
                );
            }

            // new token for the authenticated third party user
            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $user->createToken('api')->plainTextToken,
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

    public function logout(Request $request)
    {
        try {
            // user authenticated?
            // if yes, delete the current access token
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
        $user = Auth::guard('sanctum')->id() ? Auth::user('sanctum') : ThirdPartyUser::find($id);

        if (!$user || $user->getKey() != $id || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
            //Activate user after verication
            $user->IsActive = true;
            $user->save();
        }

        return response()->json(['message' => __('auth.email_verfied')], 200);
    }

    public function resendVerification(): JsonResponse
    {
        $user = Auth::user('sanctum');

        if (!$user) {
            return response()->json(['message' => __('auth.unauthenticated.')], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => __('auth. email_verified')], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => __('auth.verification_link_sent')], 200);
    }
}
