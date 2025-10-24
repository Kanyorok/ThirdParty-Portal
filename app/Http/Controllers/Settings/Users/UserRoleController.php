<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Models\Auth\ModelRole;
use App\Models\Auth\User;
use App\Models\Core\Branch;
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

        return back()->with('success', 'Role and Branch created successfully.');

    }

    /**
     * Update an existing ModelRole assignment (role and/or branch)
     */
    public function update(Request $request, ModelRole $modelRole)
    {
        // Debug: log incoming request so we can diagnose routing/matching issues
        try {
            Log::debug('UserRoleController.update entered', [
                'path' => $request->path(),
                'method' => $request->method(),
                'route' => optional($request->route())->getName(),
                'input' => $request->all(),
            ]);
        } catch (Exception $e) {
            // ignore logging failures
        }

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

        // Ensure uniqueness: no other assignment for same model and branch
        // Use composite-key safe check (t_ModelRoles may not have a ModelRoleId PK column)
        $targetCount = ModelRole::where('model_id', $modelRole->model_id)
            ->where('model_type', $modelRole->model_type)
            ->where('BranchId', $branch->Id)
            ->count();
        // If target branch is different from current and any record exists for that branch -> conflict.
        // If target branch is same as current and there's exactly one record (the current one) -> allow.
        if (!($modelRole->BranchId == $branch->Id && $targetCount === 1) && $targetCount > 0) {
            throw ValidationException::withMessages(['BranchId' => 'User already has a role in the selected branch']);
        }

        $modelRole->role_id = $role->id;
        $modelRole->BranchId = $branch->Id;
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

    public function destroy(ModelRole $modelRole)
    {
        try {
            $modelRole->delete();
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
        // Debug: log incoming request for composite-key update
        try {
            Log::debug('UserRoleController.updateByKeys entered', [
                'path' => $request->path(),
                'method' => $request->method(),
                'route' => optional($request->route())->getName(),
                'input' => $request->all(),
            ]);
        } catch (Exception $e) {
        }

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
        $targetCount = ModelRole::where('model_id', $modelRole->model_id)
            ->where('model_type', $modelRole->model_type)
            ->where('BranchId', $validated['BranchId'])
            ->count();
        if (!($modelRole->BranchId == $validated['BranchId'] && $targetCount === 1) && $targetCount > 0) {
            return back()->with('error', 'User already has a role in the selected branch');
        }

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
