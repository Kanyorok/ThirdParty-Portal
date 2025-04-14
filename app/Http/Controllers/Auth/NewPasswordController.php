<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BR\BREncryption;
use App\Services\UserService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                            'token'    => ['required'],
                            'email'    => [
                                           'required',
                                           'email',
                                          ],
                            'password' => [
                                           'required',
                                           'confirmed',
                                           Rules\Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
                                          ],
                           ]);

        $user = User::query()->where('Email', '=', $request->get('email'))->first();
        if (!$user instanceof User) {
            throw ValidationException::withMessages([
                                                     'email' => ['Confirm the the email and token are valid.'],
                                                    ]);
        }

        if (Password::tokenExists($user, $request->get('token'))) {
            if ($user->Linked) {
                (new UserService($user))->syncBR();

                Password::deleteToken($user);

                return $this->succeeded('account linked with core banking, synced use core banking password', route('home'));
            }


            $user->forceFill([
                              'Password'       => BREncryption::hashUser($user, $request->password),
                              'remember_token' => Str::random(60),
                             ])->save();

            Password::deleteToken($user);

            activity()->causedBy($user)->performedOn($user)->event('password-reset')->log('Reset password using email link.');

            Auth::login($user);
            event(new PasswordReset($user));
            activity()->causedBy($user)->performedOn($user)->event('authentication')->log('Signed in from ' . $request->getClientIp());
            return $this->succeeded('password reset successful', route('home'));
        }


        throw ValidationException::withMessages([
                                                 'email' => ['Confirm the the email and token are valid.'],
                                                ]);
    }
}
