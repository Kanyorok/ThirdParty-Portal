<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\LoginThirdPartyRequest;
use App\Http\Resources\ThirdParty\Api\ThirdPartyUserResource;
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
            'thirdParty.supplierMaster',
            'thirdParty.tenantProfile',
            'thirdParty.customerProfile',
        ])
            ->where('Email', strtolower($request->email))
            ->first();

        if (! $user || ! Hash::check($request->password, $user->Password)) {
            throw ValidationException::withMessages([
                'email' => ['Failed to authenticate.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => ('Unverified Email'),
                'requires_verification' => true,
            ], 403);
        }

        if (! $user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => ('Account inactive'),
            ], 403);
        }

        $token = $user->createToken('auth-token', ['third_party'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => ('Login Succcessful'),
            'user' => new ThirdPartyUserResource($user),
            'token' => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Load relationships
            $user->load([
                'thirdParty.types',
                'thirdParty.supplierMaster',
                'thirdParty.tenantProfile',
                'thirdParty.customerProfile',
            ]);

            return response()->json([
                'success' => true,
                'user' => new ThirdPartyUserResource($user),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in /me endpoint', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        // Get the token from request attributes (set by our custom middleware)
        $token = $request->attributes->get('sanctum_token');

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => ('Logged out successfully.'),
        ]);
    }

    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        $user = ThirdPartyUser::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link.',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified. You can now login.',
                'already_verified' => true,
            ]);
        }

        if ($user->markEmailAsVerified()) {
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully! You can now login.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to verify email. Please try again.',
        ], 500);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified.',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent! Please check your inbox.',
        ]);
    }
}
