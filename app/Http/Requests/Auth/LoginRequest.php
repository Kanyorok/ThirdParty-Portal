<?php

namespace App\Http\Requests\Auth;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\ModelRole;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HRM\Employee;
use App\Services\BR\BREncryption;
use App\Services\Core\ModuleService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
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
            'branch' => ['required', 'string', 'max:200'],
        ];
    }

    public function getBranch(User $user): array
    {
        $branch = Branch::query()->where('BranchID', $this->string('branch'))->first();
        if (!$branch instanceof Branch) {
            throw ValidationException::withMessages([
                'branch' => 'Branch not found or not authorized.',
            ]);
        }

        $modelRole = ModelRole::query()->where('model_id', $user->Id)
            ->where('model_type', User::getPrimaryKey())
            ->where('BranchId', $branch->Id)->with('role')->first();

        $role = $modelRole?->role;
        if (!$modelRole instanceof ModelRole || !$role instanceof Role) {
            throw ValidationException::withMessages([
                'branch' => 'Branch not found or not authorized.',
                //'branch' => 'You do not have access to the selected branch.',
            ]);
        }

        return ['branch' => $branch, 'role' => $role];
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
        $user = User::query()->where(function (Builder $query) {
            $query->where('UserID', $this->string('UserID')->upper()->toString())->orWhere('Email', $this->string('UserID')->lower()->toString());
        })->first();
        if ($user instanceof User && ($user->employee instanceof Employee) && BREncryption::checkAuthUser($user, $this->validated('password'))) {
            $branchRole = $this->getBranch($user);

            //check if user has a employee profile if not fail.
            RateLimiter::clear($this->throttleKey());

            //remove other sessions
            //remove other sessions
            // if (config(key: 'session.driver') === 'database') {
            //     DB::connection(config(key: 'session.connection'))->table(table: config(key: 'session.table', default: 'sessions'))
            //         ->where(column: 'user_id', operator: '=', value: $user->getAuthIdentifier())->delete();
            // }

            $user->fill([
                'last_login_at' => now(),
                'BranchId' => $branchRole['branch']->Id,
            ])->save();

            //new session
            //new session
            Auth::guard('web')->login($user, $branchRole['role']->hasPermissionTo(PermissionEnum::UsersSessions));
            $this->session()->regenerate();

            session([
                'LoginBranchId' => $branchRole['branch']->Id,
                'LoginBranchName' => $branchRole['branch']->Name,
                'LoginRoleName' => $branchRole['role']->name,
                'login_at' => $user->last_login_at,
            ]);

            activity()->causedBy($user)->performedOn($user)->event('authentication')->log('Signed in from ' . $this->getClientIp() . ' as ' . $branchRole['role']->name . ' at ' . $branchRole['branch']->Name);

            ModuleService::clearNavbarCache($user);
            
            // Force save session to DB immediately
            $this->session()->save();
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
