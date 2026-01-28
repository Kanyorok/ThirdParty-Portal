<?php

namespace App\Http\Controllers\Procurement\ThirdParties;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\LoginThirdPartyRequest;
use App\Http\Requests\ThirdParty\RegisterThirdPartyUserRequest;
use App\Http\Resources\ThirdParty\Api\ThirdPartyResource;
use App\Models\Bancassurance\BancassuranceCustomer;
use App\Models\Procurement\Suppliers\SupplierMaster;
use App\Models\Property\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\RegistrationService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            $user = $this->registrationService->registerThirdParty($request->validated());

            $token = $user->createToken('api-thirdparty')->plainTextToken;

            return response()->json([
                'message' => 'Account created successfully! Please verify your email to complete your profile.',
                'token' => $token,
                'user' => [
                    'id' => $user->Id,
                    'userId' => $user->UserID,
                    'email' => $user->Email,
                    'firstName' => $user->FirstName,
                    'lastName' => $user->LastName,
                ],
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


        try {
            $user = ThirdPartyUser::where('Email', $request->email)->first();


            if (! $user || ! Hash::check($request->password, $user->Password)) {
                throw ValidationException::withMessages([
                    'email' => __('auth.invalid_credentials'),
                ]);
            }

            if (! $user->isActive()) {
                return response()->json(['message' => __('auth.account_inactive')], 403);
            }
            if (! $user->isApproved()) {
                return response()->json(['message' => __('auth.acc_not_approved')], 403);
            }


            // Allow login for users who haven't completed setup (No ThirdPartyId)
            // They will be redirected to the setup page by the frontend
            if (empty($user->ThirdPartyId)) {
                $isAuthorized = true;
            } else {
                if ($profileType === 'Supplier') {
                    $isAuthorized = SupplierMaster::where('ThirdPartyId', $user->ThirdPartyId)
                        ->where('ApprovalStatus', ThirdPartyApprovalStatusEnum::Approved->value)
                        ->exists();
                } elseif ($profileType === 'Tenant') {
                    // Check if user is linked to an active Tenant record
                    // Assuming PropertyNewTenant maps to t_TenantMaintenance or similar active tenant table
                    $isAuthorized = PropertyNewTenant::where('ThirdPartyId', $user->ThirdPartyId)
                        ->where('IsActive', true)
                        ->exists();
                } elseif ($profileType === 'Customer') {
                    // Check if user is linked to a customer record
                    $isAuthorized = BancassuranceCustomer::where('ThirdPartyId', $user->ThirdPartyId)->exists();
                } else {
                    // Fallback or strict check
                    return response()->json(['message' => 'Profile type is required and must be valid.'], 403);
                }
            }



            if (! $isAuthorized) {
                return response()->json(['message' => 'Your account is not authorized for the selected profile type.'], 403);
            }

            $user->tokens()->delete();
            $tokenName = "api-generic-thirdparty";
            $token = $user->createToken($tokenName)->plainTextToken;

            // Load relations for resource
            $user->load(['thirdParty.types']);

            $userData = [
                'id' => $user->Id,
                'userId' => $user->UserID,
                'firstName' => $user->FirstName,
                'lastName' => $user->LastName,
                'fullName' => $user->FirstName . ' ' . $user->LastName,
                'email' => $user->Email,
                'phone' => $user->Phone,
                'imageId' => $user->ImageId,
                'gender' => $user->gender?->Name ?? $user->Gender,
                'thirdPartyId' => $user->ThirdPartyId,
                'isActive' => (bool)$user->IsActive,
                'isApproved' => $user->isApproved(),
                'isPrequalified' => $user->thirdParty ? (bool)$user->thirdParty->IsPrequalified : false,
                'isSupplier' => $user->isSupplier(),
                'emailVerifiedOn' => optional($user->EmailVerifiedOn)->format('Y-m-d H:i:s'),
                'createdOn' => optional($user->CreatedOn)->format('Y-m-d H:i:s'),
                'modifiedOn' => optional($user->ModifiedOn)->format('Y-m-d H:i:s'),
                'isTenant' => $user->isTenant(),
                'isCustomer' => $user->isCustomer(),
                // 'thirdParty' => $user->thirdParty, // Avoid full object if not needed, or simpler extraction
            ];

            // Safely extract types
            if ($user->thirdParty) {
                $userTypeData = [];
                if ($user->thirdParty->types) {
                    foreach ($user->thirdParty->types as $t) {
                        $userTypeData[] = [
                            'id' => $t->Id,
                            'code' => $t->Code,
                            'typeCategoryId' => $t->Type,
                            'label' => $t->Code,
                        ];
                    }
                }
                $userData['types'] = $userTypeData;
                $userData['thirdParty'] = $user->thirdParty; // NextAuth might expect this structure based on types definition
            }

            $responseData = [
                'user' => $userData,
                'token' => $token,
                'token_type' => 'Bearer',
            ];



            return response()->json($responseData);
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

        if (! $user) {
            return response()->json(['valid' => false], 401);
        }

        $thirdParty = $user->thirdParty;

        $isActive = $user->isApproved();

        if (! $isActive) {
            return response()->json(['valid' => false, 'message' => __('auth.account_unauthorized')], 403);
        }

        if ($thirdParty) {
            $thirdParty->setAttribute('FirstName', $user->FirstName);
            $thirdParty->setAttribute('LastName', $user->LastName);
            $thirdParty->load(['types', 'country', 'categories']);

            return response()->json([
                'valid' => true,
                'thirdParty' => new ThirdPartyResource($thirdParty),
            ]);
        }

        // If no third party, still valid user (Setup Phase)
        return response()->json([
            'valid' => true,
            'thirdParty' => null,
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

    public function verifyEmail(string $id, string $hash, Request $request): JsonResponse
    {
        // 1. Validate the user/party based on ID
        if ($request->query('type') === 'user') {
            $user = ThirdPartyUser::find($id);

            if (! $user || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
                return response()->json(['message' => __('auth.invalid_verification_link')], 403);
            }

            if (! $user->hasVerifiedEmail()) {
                if ($user->markEmailAsVerified()) {
                    event(new Verified($user));
                }
            }

            // Return JSON with User ID so frontend can redirect to profile completion
            return response()->json([
                'message' => __('auth.email_verified'),
                'user' => [
                    'id' => $user->Id,
                    'email' => $user->Email,
                ],
            ], 200);
        }

        // ... (Existing logic for 'thirdParty' entity verification if needed)
        $thirdParty = $this->resolveThirdPartyEntity($id);

        if (! $thirdParty || ! hash_equals((string) $hash, sha1($thirdParty->getEmailForVerification()))) {
            return response()->json(['message' => __('auth.invalid_verification_link')], 403);
        }

        if (! $thirdParty->hasVerifiedEmail()) {
            if ($thirdParty->markEmailAsVerified()) {
                event(new Verified($thirdParty));
            }
        }

        return response()->json([
            'message' => __('auth.email_verified'),
            'user' => [ // Fallback or maybe null if it's a party verification
                'id' => null,
            ],
        ], 200);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        // Resend for User if logged in or specified?
        // Step 1 user is logged in automatically after registration.
        // If request is from the "Check your email" screen, it's likely for the User.
        $user = Auth::guard('sanctum')->user();
        if ($user instanceof ThirdPartyUser && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return response()->json(['message' => __('auth.verification_link_sent')], 200);
        }

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

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // Check if user exists
        $user = ThirdPartyUser::where('Email', $request->email)->first();

        if (! $user) {
            // Return success even if user not found to prevent enumeration
            return response()->json(['message' => __('passwords.sent')]);
        }

        // Allow password reset regardless of approval status
        // Users should be able to reset their password even if not yet approved

        $status = Password::broker('thirdparties')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $credentials = $request->only('password', 'password_confirmation', 'token');
        $credentials['Email'] = $request->email;

        $status = Password::broker('thirdparties')->reset(
            $credentials,
            function ($user, $password) {
                $user->forceFill([
                    'Password' => Hash::make($password),
                ])->save();

                $user->setRememberToken(Str::random(60));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
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
