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

    /**
     * Display a listing of the resource.
     * @throws Exception
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return Datatables::of(Role::query()->select('*')->withCount('users'))->addIndexColumn()
                ->addColumn('action', function (Role $role) {
                    return '<button type="button" data-click_url="' . route('roles.edit', [$role->id]) . '" data-summary_title="Role ' . $role->name . '" class="btn btn-primary btn-sm click-summary-data"><i class="fas fa-edit"></i></button>&nbsp;
                        <button type="button" data-info="' . route('roles.destroy', [$role->id]) . '~' . $role->name . '" class="btn btn-danger btn-sm trash-role"><i class="fas fa-trash"></i> </button>';
                })->editColumn('created_at', function (Role $role) {
                    return $role->created_at->format('d M Y, H:i');
                })->editColumn('users_count', function (Role $role) {
                    return Number::abbreviate($role->users_count, ($role->users_count > 999) ? 1 : 0);
                })->addColumn('creator', function (Role $role) {
                    if (is_null($role->CreatedBy)) {
                        return '?';
                    }
                    $user = User::withTrashed()->where('Id', $role->CreatedBy)->first();
                    if (!$user instanceof User) {
                        return '?';
                    }
                    return $user->UserID . '-' . $user->Name;
                })->setRowClass('mouse_pointer user-select-none dbl-click-summary-data')->setRowData([
                    'dbl_click_url' => function (Role $role) {
                        return route('roles.edit', [$role->id]);
                    },
                    'summary_title' => function (Role $role) {
                        return "Role " . $role->name;
                    },
                ])->rawColumns(['action'])->make();
        }

        return view('settings.roles.index');
    }

    /*
      * Show the form for creating a new resource.
      */

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(RoleRequest $request): JsonResponse
    {
        $permissions = $request->getPermissions();
        $name = $request->getName();
        $actor = $request->user();
        try {
            DB::transaction(static function () use ($actor, $name, $permissions) {
                $Role = Role::create([
                    'name' => $name,
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                ]);
                $Role->permissions()->syncWithPivotValues($permissions, ['CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id], false);
                activity()->causedBy($actor)->performedOn($Role)->event('create')->log('create role ' . $Role->name);
            });
        } catch (Exception $e) {
            Log::error('Error creating role failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('role created successfully');
    }

    public function create(): View
    {
        return view('settings.roles.create');
    }

    /**
     * Display the specified resource.
     *
     * public function show(Role $role)
     * {
     * //
     * }*/

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): View
    {
        return view('settings.roles.edit', compact('role'))
            ->with('permissions', $role->permissions()->select('t_Permissions.name')->get('name')->pluck('name')->toArray());
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $permissions = $request->getPermissions();
        $name = $request->getName($role);
        $actor = $request->user();

        try {
            DB::transaction(static function () use ($role, $actor, $name, $permissions) {
                $role->update([
                    'name' => $name,
                    'ModifiedBy' => $actor->Id,
                ]);

                $role->permissions()->syncWithPivotValues($permissions, ['CreatedBy' => $actor->Id, 'ModifiedBy' => $actor->Id]);
                activity()->causedBy($actor)->performedOn($role)->event('update')->log('update role ' . $role->name);
            });
        } catch (Exception $e) {
            Log::error('Error updating role failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        if ($role->users()->count() > 0) {
            return $this->errored('cannot delete role with users');
        }

        try {
            DB::transaction(static function () use ($request, $role) {
                $role->delete();

                activity()->causedBy($request->user())->performedOn($role)->event('delete')->log('deleted role ' . $role->name);
            });
        } catch (Exception $e) {
            Log::error('Error delete role failed: ' . $e->getMessage());
            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('role deleted successfully');
    }
}
