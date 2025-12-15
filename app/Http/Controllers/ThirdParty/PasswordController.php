<?php

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdPartyAuth\ChangePasswordRequest;
use App\Http\Requests\ThirdPartyAuth\ForgotPasswordRequest;
use App\Http\Requests\ThirdPartyAuth\ResetPasswordRequest;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function update(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user('third_party');

        if (!Hash::check($request->current_password, $user->Password)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.current_password_incorrect'),
                'errors' => [
                    'current_password' => [__('auth.current_password_incorrect')],
                ],
            ], 422);
        }

        DB::transaction(function () use ($user, $request) {
            $user->update([
                'Password' => $request->password,
                'ModifiedBy' => $user->Id,
            ]);

            $user->tokens()
                ->where('id', '!=', $user->currentAccessToken()->id)
                ->delete();
        });

        return response()->json([
            'success' => true,
            'message' => __('auth.password_updated'),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = ThirdPartyUser::where('Email', strtolower($request->email))->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('t_ThirdPartyPasswordResets')->updateOrInsert(
                ['Email' => $user->Email],
                [
                    'Token' => Hash::make($token),
                    'CreatedOn' => now(),
                ]
            );

            $user->sendPasswordResetNotification($token);
        }

        return response()->json([
            'success' => true,
            'message' => __('auth.password_reset_link_sent'),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $reset = DB::table('t_ThirdPartyPasswordResets')
            ->where('Email', strtolower($request->email))
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->Token)) {
            return response()->json([
                'success' => false,
                'message' => __('auth.invalid_reset_token'),
            ], 422);
        }

        if (now()->diffInMinutes($reset->CreatedOn) > 60) {
            DB::table('t_ThirdPartyPasswordResets')
                ->where('Email', $request->email)
                ->delete();

            return response()->json([
                'success' => false,
                'message' => __('auth.reset_token_expired'),
            ], 422);
        }

        $user = ThirdPartyUser::where('Email', strtolower($request->email))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.user_not_found'),
            ], 404);
        }

        DB::transaction(function () use ($user, $request) {
            $user->update([
                'Password' => $request->password,
                'ModifiedBy' => $user->Id,
            ]);

            $user->tokens()->delete();

            DB::table('t_ThirdPartyPasswordResets')
                ->where('Email', $user->Email)
                ->delete();
        });

        return response()->json([
            'success' => true,
            'message' => __('auth.password_reset_success'),
        ]);
    }
}
