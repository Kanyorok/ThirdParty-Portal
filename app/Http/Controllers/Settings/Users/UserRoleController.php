<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Models\Auth\ModelRole;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Services\Core\ModuleService;
use App\Services\HRM\UserService;
use Exception;
use Illuminate\Support\Facades\Log;
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
        ModuleService::clearNavbarCache($user);

        return back()->with('success', 'Role and Branch created successfully.');
    }

    /**
     * Update an existing ModelRole assignment (role and/or branch)
     */
    public function update(Request $request, ModelRole $modelRole)
    {


        try {
            $validated = $request->validate([
                'role_id' => ['required', 'string'],
                'BranchId' => ['required', 'exists:t_Branches,Id'],
            ]);

        try {
            $validated = $request->validate([
                'role_id' => ['required', 'string'],
                'BranchId' => ['required', 'exists:t_Branches,Id'],
            ]);

            $role = Role::query()->where('id', $validated['role_id'])->first();
            $branch = Branch::query()->where('Id', $validated['BranchId'])->first();

            if (!$role instanceof Role) {
                throw ValidationException::withMessages(['Role' => 'invalid role defined']);
            }

            // Enforce uniqueness: no other assignment for same model and branch
            $conflictingRole = ModelRole::where('model_id', $modelRole->model_id)
                ->where('model_type', $modelRole->model_type)
                ->where('BranchId', $branch->Id)
                ->where('ModelRoleId', '!=', $modelRole->ModelRoleId) // Exclude self
                ->exists();

            if ($conflictingRole) {
                throw ValidationException::withMessages(['BranchId' => 'User already has a role in the selected branch']);
            }

            $modelRole->role_id = $role->id;
            $modelRole->BranchId = $branch->Id;
            $modelRole->save();
            ModuleService::clearNavbarCache($request->user());

            if ($request->ajax()) {
                return response()->json(['message' => 'Role assignment updated successfully.']);
            }

            return back()->with('success', 'Role assignment updated successfully.');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage() ?: 'Server error'], 500);
            }
            throw $e;
        }
    }

    public function destroy(ModelRole $modelRole)
    {

        try {
            $modelRole->delete();
            ModuleService::clearNavbarCache(request()->user());
            if (request()->ajax()) {
                return response()->json(['message' => 'Role assignment deleted successfully.']);
            }
            return back()->with('success', 'Role assignment deleted successfully.');
        } catch (Exception $e) {
            if (request()->ajax()) {
                return response()->json(['message' => 'Failed to delete role assignment.'], 500);
            }
            return back()->with('error', 'Failed to delete role assignment.');
        }
    }

    /**
     * Delete ModelRole by composite identifying keys when ModelRoleId is not available
     */
    public function destroyByKeys(Request $request)
    {
        $validated = $request->validate([
            'model_id' => ['required'],
            'model_type' => ['required', 'string'],
            'BranchId' => ['required', 'integer'],
            'role_id' => ['sometimes', 'integer'],
        ]);

        $query = ModelRole::where('model_id', $validated['model_id'])
            ->where('model_type', $validated['model_type'])
            ->where('BranchId', $validated['BranchId']);

        if (!empty($validated['role_id'])) {
            $query->where('role_id', $validated['role_id']);
        }

        $modelRole = $query->first();
        if (!$modelRole instanceof ModelRole) {
            return back()->with('error', 'Role assignment not found.');
        }

        try {
            $modelRole->delete();
            ModuleService::clearNavbarCache($request->user());
            if ($request->ajax()) {
                return response()->json(['message' => 'Role assignment deleted successfully.']);
            }
            return back()->with('success', 'Role assignment deleted successfully.');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Failed to delete role assignment.'], 500);
            }
            return back()->with('error', 'Failed to delete role assignment.');
        }
    }

    /**
     * Update ModelRole by composite keys when ModelRoleId is not available
     */
    public function updateByKeys(Request $request)
    {


        try {
            $validated = $request->validate([
                'model_id' => ['required'],
                'model_type' => ['required', 'string'],
                'BranchId' => ['required', 'integer'],
                'role_id' => ['required', 'integer'],
            ]);

        try {
            $validated = $request->validate([
                'model_id' => ['required'],
                'model_type' => ['required', 'string'],
                'BranchId' => ['required', 'integer'],
                'role_id' => ['required', 'integer'],
            ]);

            $modelRole = ModelRole::where('model_id', $validated['model_id'])
                ->where('model_type', $validated['model_type'])
                ->where('BranchId', $validated['BranchId'])
                ->first();

            if (!$modelRole instanceof ModelRole) {
                return back()->with('error', 'Role assignment not found.');
            }

            // Ensure uniqueness for target branch using composite-key-safe logic
            // We need to make sure we are not conflicting with ANOTHER record
            // Since we don't have ModelRoleId here easily (we fetched $modelRole by composite keys), ensuring no *other* record exists is tricky without a PK.
            // However, we fetched $modelRole using the OLD branch ID.
            // If the NEW branch ID is different, we check if ANY record exists for the NEW branch.
            if ($modelRole->BranchId != $validated['BranchId']) {
                $exists = ModelRole::where('model_id', $modelRole->model_id)
                    ->where('model_type', $modelRole->model_type)
                    ->where('BranchId', $validated['BranchId'])
                    ->exists();
                if ($exists) {
                    return back()->with('error', 'User already has a role in the selected branch');
                }
            }
            // If BranchId is the same, we are just updating the role_id, which is fine (uniqueness of branch preserved).    }

            $modelRole->role_id = $validated['role_id'];
            $modelRole->BranchId = $validated['BranchId'];
            $modelRole->save();

            if ($request->ajax()) {
                return response()->json(['message' => 'Role assignment updated successfully.']);
            }

            return back()->with('success', 'Role assignment updated successfully.');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json(['message' => $e->getMessage() ?: 'Server error'], 500);
            }
            throw $e;
        }
    }
}
