<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Http\Requests\ThirdParty\RegisterThirdPartyUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ThirdPartyAuthController extends Controller
{
    public function register(RegisterThirdPartyUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $userData = [
                'UserID' => $data['UserID'],
                'FirstName' => $data['FirstName'],
                'LastName' => $data['LastName'],
                'Email' => $data['Email'],
                'Phone' => $data['Phone'],
                'Password' => Hash::make($data['Password']),
                'ThirdPartyId' => $data['ThirdPartyId'],
                'CreatedBy' => Auth::id(),
                'ModifiedBy' =>  Auth::id(),
                'ModifiedOn'    => now(),
            ];

            $user = ThirdPartyUser::create($userData);

            $token = $user->createToken('api')->plainTextToken;

            return response()->json([
                'user' => new ThirdPartyUserResource($user),
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('auth.registration_failed'),
                'error' => $e->getMessage(),
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
}
