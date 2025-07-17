<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Http\Requests\ThirdParty\RegisterThirdPartyUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ThirdPartyAuthController extends Controller
{
    public function register(RegisterThirdPartyUserRequest $request)
    {
        $data = $request->validated();

        $userData = [
            'UserID' => Str::uuid(),
            'FirstName' => $data['firstName'],
            'LastName' => $data['lastName'],
            'Email' => $data['email'],
            'Phone' => $data['phone'],
            'Password' => Hash::make($data['password']),
            'ThirdPartyId' => $data['thirdPartyId'],
            'CreatedBy' => Auth::id(),
            'ModifiedBy' =>  Auth::id(),
        ];

        $user = ThirdPartyUser::create($userData);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }


    public function login(Request $request)
    {
        $user = ThirdPartyUser::where('Email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->Password)) {
            throw ValidationException::withMessages(
                [
                    'email' => __('auth.invalid_credentials')
                ]
            );
        }

        if (! $user->isApproved()) {
            return response()->json(
                [
                    'message' => __('auth.acc_not_approved')
                ],
                403
            );
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => __('auth.logout_successful')]);
    }
}
