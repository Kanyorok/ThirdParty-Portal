<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\BR\BREncryption;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'UserID' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $user = User::query()->where('UserID', $this->string('UserID')->upper()->toString())->first();
        if ($user instanceof User && BREncryption::checkAuthUser($user, $this->get('password'))) {
            RateLimiter::clear($this->throttleKey());
            //remove other sessions
            if (config(key: 'session.driver') === 'database') {
                DB::connection(config(key: 'session.connection'))->table(table: config(key: 'session.table', default: 'sessions'))
                    ->where(column: 'user_id', operator: '=', value: $user->getAuthIdentifier())->delete();
            }


            //new session
            Auth::login($user, $user->can(\App\Enums\Core\PermissionEnum::UsersSessions));
            $this->session()->regenerate();
            activity()->causedBy($user)->performedOn($user)->event('authentication')->log('Signed in from ' . $this->getClientIp());
            return;
        }

        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages([
            'UserID' => trans('auth.failed'),
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'UserID' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        return Str::lower($this->input('UserID')) . '|' . $this->ip();
    }
}
