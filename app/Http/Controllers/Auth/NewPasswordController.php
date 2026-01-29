<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Services\BR\BREncryption;
use App\Services\HRM\UserService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only('store');
    }

    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
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

        $user = User::where('Email', $request->email)->first();

        if (! $user || ! Password::tokenExists($user, $request->token)) {
            throw ValidationException::withMessages([
                'email' => ['Confirm the email and token are valid.'],
            ]);
        }

        if ($user->Linked) {
            (new UserService($user))->syncBR();
            Password::deleteToken($user);

            return $this->succeeded('Account linked with core banking, synced. Use core banking password.', route('home'));
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
            ->log('Reset password using email link.');

        return $this->succeeded('Password reset successful. Please log in and select a branch.', route('login'));
    }
}
