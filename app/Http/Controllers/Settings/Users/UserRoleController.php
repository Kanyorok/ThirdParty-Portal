<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request, User $user): View
    {
        return view('settings.users.role', compact('user'))->with('Roles', Role::all());
    }

    /**
     * Handle the incoming request.
     * @throws ValidationException
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $request->validate([
                            'Role' => [
                                       'required',
                                       'string',
                                      ],
                           ]);
        $role = Role::query()->where('t_Roles.id', $request->Role)->first();
        if (!$role instanceof Role) {
            throw ValidationException::withMessages(['Role' => 'invalid role defined']);
        }

        (new UserService($user))->setRole($role);

        return $this->succeeded('role updated successfully', route('users.show', [$user->UserID]));
    }
}
