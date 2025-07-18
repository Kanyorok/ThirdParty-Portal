<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Http\Requests\ThirdParty\RegisterThirdPartyUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use Illuminate\Support\Facades\Auth;

class ThirdPartyAuthController extends Controller
{
    public function register(RegisterThirdPartyUserRequest $request)
    {
        $data = $request->validated();

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
        ]);
    }


    public function login(Request $request)
    {
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
    }

    public function logout(Request $request)
    {
        // user authenticated?
        // if yes, delete the current access token
        if (Auth::guard('sanctum')->check()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => __('auth.logout_successful')]);
        }
        return response()->json(['message' => __('auth.not_authenticated')], 401);
    }
}
