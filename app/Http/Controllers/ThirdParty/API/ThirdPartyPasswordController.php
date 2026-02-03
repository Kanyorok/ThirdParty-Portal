<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\HRM\UserService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class ThirdPartyPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only(['resetPassword', 'forgotPassword']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('thirdparties')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->succeeded(__($status))
            : throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
        ]);

        $broker = Password::broker('thirdparties');
        $user = ThirdPartyUser::where('Email', $request->email)->first();

        if (! $user || ! $broker->tokenExists($user, $request->token)) {
            throw ValidationException::withMessages([
                'email' => ['Confirm the email and token are valid.'],
            ]);
        }

        if ($user->Linked) {
            (new UserService($user))->syncBR();
            $broker->deleteToken($user);

            return $this->succeeded('Account linked with core banking, synced. Use core banking password.');
        }

        $user->forceFill([
            'Password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        $broker->deleteToken($user);
        event(new PasswordReset($user));

        if (function_exists('activity')) {
            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->event('password-reset')
                ->log('Third-party reset password using email link.');
        }

        return $this->succeeded('Password reset successful. You can now log in.');
    }

    public function succeeded(string $message, string $route = '', array $data = [], int $status = 202): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'route' => $route,
            'data' => $data,
        ], $status);
    }
}
