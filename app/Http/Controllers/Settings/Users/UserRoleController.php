<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Models\Auth\ModelRole;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Services\HRM\UserService;
use Exception;
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
    public function store(Request $request, User $user)
    {

        $validated = $request->validate([
            'role_id' => ['required', 'string'],
            'BranchId' => ['required', 'exists:t_Branches,Id'],
        ]);

    $role = Role::query()->where('id', $validated['role_id'])->first(); // assuming alias or exact table

        // Fetch the correct Branch model (App\Models\Core\Branch)
        /** @var Branch|null $branch */
        $branch = Branch::query()->where('Id', $validated['BranchId'])->first();

        if (!$role instanceof Role) {
            throw ValidationException::withMessages(['Role' => 'invalid role defined']);
        }

        // Ensure the user does not already have a role in this branch
        // Note: ModelRole.model_id uses the numeric user Id (User->Id) and model_type uses the user's primary key name
        $existing = ModelRole::where('model_id', $user->Id)
            ->where('model_type', $user::getPrimaryKey())
            ->where('BranchId', $branch->Id)
            ->first();
        if ($existing) {
            throw ValidationException::withMessages(['BranchId' => 'User already has a role in the selected branch']);
        }

        (new UserService($user))->setRole($role, $branch, $request->user());

        return back()->with('success', 'Role and Branch created successfully.');

    }

    /**
     * Update an existing ModelRole assignment (role and/or branch)
     */
    public function update(Request $request, ModelRole $modelRole)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'string'],
            'BranchId' => ['required', 'exists:t_Branches,Id'],
        ]);

        $role = Role::query()->where('id', $validated['role_id'])->first();
        $branch = Branch::query()->where('Id', $validated['BranchId'])->first();

        if (!$role instanceof Role) {
            throw ValidationException::withMessages(['Role' => 'invalid role defined']);
        }

        // Ensure uniqueness: no other assignment for same model and branch
        $exists = ModelRole::where('model_id', $modelRole->model_id)
            ->where('model_type', $modelRole->model_type)
            ->where('BranchId', $branch->Id)
            ->where('ModelRoleId', '!=', $modelRole->ModelRoleId)
            ->exists();
        if ($exists) {
            throw ValidationException::withMessages(['BranchId' => 'User already has a role in the selected branch']);
        }

        $modelRole->role_id = $role->id;
        $modelRole->BranchId = $branch->Id;
        $modelRole->save();

        return back()->with('success', 'Role assignment updated successfully.');
    }

    public function destroy(ModelRole $modelRole)
    {
        try {
            $modelRole->delete();
            return back()->with('success', 'Role assignment deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete role assignment.');
        }
    }
}
