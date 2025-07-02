<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
        // Perform authentication
        $request->authenticate();
        $user = $request->user();

        // Clear navbar cache
        ModuleService::clearNavbarCache($user);

        // Validate branch selection
        $selectedBranchId = $request->input('branch');

        if (empty($selectedBranchId)) {
            Auth::logout(); // Logout since session is authenticated but invalid
            return redirect()->back()->withErrors([
                'branch' => 'You must select a login branch.'
            ]);
        }

        // Check if selected branch is valid for this user via ModelRoles
        $hasAccess = DB::table('t_ModelRoles')
            ->where('model_id', $user->Id)
            ->where('BranchId', $selectedBranchId)
            ->exists();

        if (!$hasAccess) {
            Auth::logout();
            return redirect()->back()->withErrors([
                'branch' => 'You do not have access to the selected branch.'
            ]);
        }

        // Get branch name
        $branchName = DB::table('t_Branches')
            ->where('Id', $selectedBranchId)
            ->value('Name');

        // Store in session
        session([
            'LoginBranchId' => $selectedBranchId,
            'LoginBranchName' => $branchName
        ]);

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
