<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\BR\BREncryption;
use App\Services\HRM\UserService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class ThirdPartyPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only(['store', 'forgotPassword']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->succeeded(__($status))
            : throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
    }

    public function store(Request $request): JsonResponse
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

        $user = ThirdPartyUser::where('Email', $request->email)->first();

        if (!$user || !Password::tokenExists($user, $request->token)) {
            throw ValidationException::withMessages([
                'email' => ['Confirm the email and token are valid.'],
            ]);
        }

        if ($user->Linked) {
            (new UserService($user))->syncBR();
            Password::deleteToken($user);
            return $this->succeeded('Account linked with core banking, synced. Use core banking password.');
        }

        $user->forceFill([
            'Password' => BREncryption::hashUser($user, $request->password),
            'remember_token' => Str::random(60),
        ])->save();

        Password::deleteToken($user);
        event(new PasswordReset($user));

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->event('password-reset')
            ->log('Third-party reset password using email link.');

        return $this->succeeded('Password reset successful. You can now log in.');
    }
}
