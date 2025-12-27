<?php

namespace App\Http\Controllers\Procurement\ThirdParties;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Http\Requests\ThirdParty\RegisterThirdPartyUserRequest;
use App\Http\Requests\ThirdPartyAuth\LoginThirdPartyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ThirdParty\ThirdPartyResource;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use App\Models\ThirdParty\ThirdParties;

// @Kimxons
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
            $thirdParty = $this->registrationService->registerThirdParty($request->validated());

            $user = ThirdPartyUser::where('ThirdPartyId', $thirdParty->Id)->first();

            return response()->json([
                'message' => __('auth.registration_successful'),
                'userId' => $user->UserID,
                'thirdPartyId' => $thirdParty->Id,
            ], 201);
        } catch (\Exception $e) {
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
        // $requestedProfileLabel = $request->profile_type;

        try {
            $user = ThirdPartyUser::where('Email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->Password)) {
                throw ValidationException::withMessages([
                    'email' => __('auth.invalid_credentials')
                ]);
            }

            if (!$user->IsActive) {
                return response()->json(['message' => __('auth.account_inactive')], 403);
            }

            // $thirdParty = ThirdParties::find($user->ThirdPartyId);

            if (!$thirdParty) {
                // Individual user scenario: unlinked to any Third Party
                if (!$user->isApproved()) {
                    return response()->json(['message' => __('auth.account_unauthorized')], 403);
                }
            } else {
                // Check ThirdParty approval status for linked users
                if (!$thirdParty->isApproved()) {
                    return response()->json(['message' => __('auth.third_party_not_approved')], 403);
                }
            }

            $thirdParty->setAttribute('FirstName', $user->FirstName);
            $thirdParty->setAttribute('LastName', $user->LastName);

            /*
            $requiredInternalCode = null;
            foreach (ThirdPartyTypeEnum::cases() as $type) {
                if ($type->label() === $requestedProfileLabel) {
                    $requiredInternalCode = $type->value;
                    break;
                }
            }

            if (!$requiredInternalCode) {
                throw ValidationException::withMessages([
                    'profile_type' => __('auth.invalid_profile_selection')
                ]);
            }

            $thirdParty->load('types');

            $hasRequestedType = $thirdParty->types->contains(function ($type) use ($requiredInternalCode) {
                $codePrefix = Str::upper($requiredInternalCode);
                return str_starts_with($type->Code, $codePrefix);
            });

            if (!$hasRequestedType) {
                throw ValidationException::withMessages([
                    'profile_type' => __("auth.account_not_a_{$requestedProfileLabel}")
                ]);
            }
            */

            $user->tokens()->delete();
            $tokenName = "api-generic-thirdparty";
            $token = $user->createToken($tokenName)->plainTextToken;

            $thirdParty->load(['types', 'country', 'categories']);

            $resource = (new ThirdPartyResource($thirdParty))->additional([
                'meta' => [
                    // 'selected_profile_type' => $requestedProfileLabel, // Commented out
                    // Frontend will determine the selected profile type based on user interaction after login
                ]
            ]);

            return response()->json([
                'thirdParty' => $resource,
                'token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Third Party Login Failed: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => __('auth.login_failed'),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function validateToken(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['valid' => false], 401);
        }

        $thirdParty = $user->thirdParty;

        $isActive = $user->isApproved();

        if (!$isActive) {
            return response()->json(['valid' => false, 'message' => __('auth.account_unauthorized')], 403);
        }

        $thirdParty->setAttribute('FirstName', $user->FirstName);
        $thirdParty->setAttribute('LastName', $user->LastName);

        $thirdParty->load(['types', 'country', 'categories']);

        return response()->json([
            'valid' => true,
            'thirdParty' => new ThirdPartyResource($thirdParty),
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
        $thirdParty = $this->resolveThirdPartyEntity($id);

        if (! $thirdParty || ! hash_equals((string) $hash, sha1($thirdParty->getEmailForVerification()))) {
            return response()->json(['message' => __('auth.invalid_verification_link')], 403);
        }

        if ($thirdParty->hasVerifiedEmail()) {
            return response()->json(['message' => __('auth.email_already_verified')], 200);
        }

        if ($thirdParty->markEmailAsVerified()) {
            event(new Verified($thirdParty));
        }

        return response()->json(['message' => __('auth.email_verified')], 200);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $thirdParty = $this->resolveThirdPartyEntity($request->third_party_id);

        if (! $thirdParty) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        if ($thirdParty->hasVerifiedEmail()) {
            return response()->json(['message' => __('auth.email_already_verified')], 400);
        }

        $thirdParty->sendEmailVerificationNotification();

        return response()->json(['message' => __('auth.verification_link_sent')], 200);
    }

    private function resolveThirdPartyEntity(?string $id = null): ?ThirdParties
    {
        $authenticatedUser = Auth::guard('sanctum')->user();

        if ($authenticatedUser instanceof ThirdPartyUser) {
            return $authenticatedUser->thirdParty;
        }

        return $id ? ThirdParties::where('Id', $id)->first() : null;
    }
}
