<?php

namespace App\Http\Middleware;

use App\Models\Auth\ModelRole;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$guards
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {

        $this->authenticate($request, $guards);
        $actor = $request->user();
        $branch = $actor->branch;
        if (!$actor instanceof User || !$branch instanceof Branch) {
            $this->unauthenticated($request, $guards, $actor);
        }

        if (session()?->has('branch_id') === false) {
            $modelRole = ModelRole::query()->where('model_id', $actor->Id)
                ->where('model_type', User::getPrimaryKey())
                ->where('BranchId', $branch->Id)->with('role')->first();
            $role = $modelRole?->role;
            if (!$modelRole instanceof ModelRole || !$role instanceof Role) {
                $this->unauthenticated($request, $guards);
            }

            session([
                'LoginBranchId' => $branch->Id,
                'LoginBranchName' => $branch->Name,
                'LoginRoleName' => $role->name,
                'login_at' => $actor->last_login_at,
            ]);
        }

        return $next($request);
    }

    protected function unauthenticated($request, array $guards, User $user = null): void
    {
        if ($user instanceof User) {
            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->event('authentication')
                ->log('Invalid session from ' . $request->getClientIp());
            $user->update([
                'BranchId' => null,
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $request->expectsJson() ? null : $this->redirectTo($request),
        );
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() || $request->json() ? null : route('login');
    }

}
