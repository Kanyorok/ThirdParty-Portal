<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RoleRequest;
use App\Models\Auth\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['index']);
        $this->authorizeResource(Role::class);
    }

    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return DataTables::of(Role::query()->select('*')->withCount('users'))->addIndexColumn()
                ->addColumn('action', function (Role $role) {
                    return '<button type="button" data-click_url="' . route('roles.edit', [$role->id]) . '" data-summary_title="Role ' . $role->name . '" class="btn btn-primary btn-sm click-summary-data" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>&nbsp;
                            <button type="button" data-info="' . route('roles.destroy', [$role->id]) . '~' . $role->name . '" class="btn btn-danger btn-sm trash-role" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>';
                })
                ->editColumn('created_at', function (Role $role) {
                    return $role->created_at?->format('d M Y, H:i') ?? '-';
                })
                ->editColumn('users_count', function (Role $role) {
                    $count = Number::abbreviate($role->users_count, ($role->users_count > 999) ? 1 : 0);
                    return '<a href="#" class="view-role-users" data-role_id="' . $role->id . '" title="View Users">' . $count . '</a>';
                })
                ->addColumn('creator', function (Role $role) {
                    if (is_null($role->CreatedBy)) {
                        return '?';
                    }
                    $user = User::withTrashed()->find($role->CreatedBy);
                    return $user ? $user->UserID . '-' . $user->Name : '?';
                })
                ->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')
                ->setRowData([
                    'dbl_click_url' => fn(Role $role) => route('roles.edit', [$role->id]),
                    'summary_title' => fn(Role $role) => 'Role ' . $role->name,
                ])
                ->rawColumns(['action', 'users_count'])
                ->make();
        }

        return view('settings.roles.index');
    }

    public function showAjax($id): View
    {
        $role = Role::with('users')->find($id);

        if (!$role) {
            abort(404, 'Role not found.');
        }

        return view('settings.roles.partials.show', compact('role'));
    }

    public function create(): View
    {
        return view('settings.roles.create');
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $permissions = $request->getPermissions();
        $name = $request->getName();
        $actor = $request->user();

        try {
            DB::transaction(function () use ($actor, $name, $permissions) {
                $role = Role::create([
                    'name' => $name,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);

                $role->permissions()->syncWithPivotValues(
                    $permissions,
                    ['CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id],
                    false
                );

                activity()->causedBy($actor)->performedOn($role)->event('create')->log('create role ' . $role->name);
            });
        } catch (Exception $e) {
            Log::error('Error creating role: ' . $e->getMessage());
            return $this->errored('Unexpected error, try again later.');
        }

        return $this->succeeded('Role created successfully');
    }

    public function edit(Role $role): View
    {
        $permissions = $role->permissions()->pluck('name')->toArray();
        return view('settings.roles.edit', compact('role', 'permissions'));
    }

    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $permissions = $request->getPermissions();
        $name = $request->getName($role);
        $actor = $request->user();

        try {
            DB::transaction(function () use ($role, $actor, $name, $permissions) {
                $role->update([
                    'name' => $name,
                    'ModifiedBy' => $actor->Id,
                ]);

                $role->permissions()->syncWithPivotValues(
                    $permissions,
                    ['CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id]
                );

                activity()->causedBy($actor)->performedOn($role)->event('update')->log('update role ' . $role->name);
            });
        } catch (Exception $e) {
            Log::error('Error updating role: ' . $e->getMessage());
            return $this->errored('Unexpected error, try again later.');
        }

        return $this->succeeded('Role updated successfully');
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        if ($role->users()->count() > 0) {
            return $this->errored('Cannot delete role with users assigned.');
        }

        try {
            DB::transaction(function () use ($request, $role) {
                $role->delete();
                activity()->causedBy($request->user())->performedOn($role)->event('delete')->log('Deleted role ' . $role->name);
            });
        } catch (Exception $e) {
            Log::error('Error deleting role: ' . $e->getMessage());
            return $this->errored('Unexpected error, try again later.');
        }

        return $this->succeeded('Role deleted successfully');
    }
}
