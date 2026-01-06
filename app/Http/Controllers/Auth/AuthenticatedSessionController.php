<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Core\Branch;
use App\Services\Core\ModuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only('store');
    }

    public function create(): View
    {
        return view('auth.login')
            ->with('branches', Branch::query()->orderBy('Name')->get(['BranchID', 'Name']));
    }

    public function store(LoginRequest $request): JsonResponse
    {
        // Authenticate
        $request->authenticate();

        return $this->succeeded(message: 'Logged in successfully.', route: route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $actor = $request->user();
        activity()
            ->causedBy($actor)
            ->performedOn($actor)
            ->event('authentication')
            ->log('Signed Out from ' . $request->getClientIp());

        ModuleService::clearNavbarCache($actor);
        $actor->update([
            'BranchId' => null,
        ]);

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
