<?php

namespace App\Http\Controllers\Settings\Users;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Auth\ModelRole;
use App\Models\Core\Branch;
use App\Services\HRM\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

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
        $validated = $request->validate([
            'role_id' => ['required', 'string'],
            'BranchId' => ['required', 'exists:t_Branches,Id'],
        ]);

        $role = Role::query()->where('t_Roles.id', $validated['role_id'])->first(); // assuming alias or exact table

        // Fetch the correct Branch model (App\Models\Core\Branch)
        /** @var \App\Models\Core\Branch|null $branch */
        $branch = Branch::query()->where('Id', $validated['BranchId'])->first();

        if (!$role instanceof Role) {
            throw ValidationException::withMessages(['Role' => 'invalid role defined']);
        }

        (new UserService($user))->setRole($role, $branch, $request->user());

        return $this->succeeded('role updated successfully', route('users.show', [$user->UserID]));
    }

    public function storeBranch(Request $request)
    {
        dd($request->all());
        $validator = Validator::make($request->all(), [
            'model_id' => 'required|exists:t_Users,UserID',
            'model_type' => 'required|in:App\Models\Auth\User',
            'BranchId' => 'required|exists:t_Branches,Id',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        ModelRole::create([
            'model_id' => $request->model_id,
            'model_type' => $request->model_type,
            'role_id' => $request->role_id,
            'BranchId' => $request->BranchId,
        ]);

        return back()->with('success', 'Branch role assigned successfully.');
    }
}
