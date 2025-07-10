<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Auth\User;
use App\Services\Core\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show login form with branch selection.
     */
    public function create(): View
    {
        // Fetch all branches
        $branches = DB::table('t_Branches')->select('Id', 'Name')->get();

        return view('auth.login', compact('branches'));
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param LoginRequest $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Authenticate
        $request->authenticate();
        $user = $request->user();

        ModuleService::clearNavbarCache($user);

        $selectedBranchId = $request->input('branch');
        if (empty($selectedBranchId)) {
            Auth::logout();
            return redirect()->back()->withErrors([
                'branch' => 'You must select a login branch.'
            ]);
        }

        $modelRole = DB::table('t_ModelRoles')
            ->where('model_id', $user->Id)
            ->where('model_type', User::getPrimaryKey()) // resolves to 'Id'
            ->where('BranchId', $selectedBranchId)
            ->first();

        if (!$modelRole) {
            Auth::logout();
            return redirect()->back()->withErrors([
                'branch' => 'You do not have access to the selected branch.'
            ]);
        }

        $roleName = DB::table('t_Roles')
            ->where('id', $modelRole->role_id)
            ->value('name');

        if (!$roleName) {
            Auth::logout();
            return redirect()->back()->withErrors([
                'branch' => 'Role mapping not found for selected branch.'
            ]);
        }

        $branchName = DB::table('t_Branches')
            ->where('Id', $selectedBranchId)
            ->value('Name');

        session([
            'LoginBranchId' => $selectedBranchId,
            'LoginBranchName' => $branchName,
            'LoginRoleName' => $roleName
        ]);

        $user->setEffectiveRole($roleName); // Tell Spatie the effective role

        return redirect()->intended('/');
    }


    /**
     * Logout
     */
    public function destroy(Request $request): RedirectResponse
    {
        activity()
            ->causedBy($request->user())
            ->performedOn($request->user())
            ->event('authentication')
            ->log('Signed Out from ' . $request->getClientIp());

        ModuleService::clearNavbarCache($request->user());

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Logout on timeout
     */
    public function timeout(Request $request): JsonResponse
    {
        activity()
            ->causedBy($request->user())
            ->performedOn($request->user())
            ->event('authentication')
            ->log('Session timeout after 1 minute on ' . $request->getClientIp());

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $request->session()->put('status', 'Session timeout, please login again.');

        return $this->succeeded('Session timeout, please login again.');
    }
}
