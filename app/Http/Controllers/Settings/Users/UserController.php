<?php

namespace App\Http\Controllers\Settings\Users;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRequest;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HR\Employee;
use App\Services\HRM\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->except(['show', 'index']);
        $this->authorizeResource(User::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            try {
                return UserService::dt(User::query(), ['photo']);
            } catch (Exception $e) {
            }

            return $this->errored('unexpected error, try again later');
        }

        return view('settings.users.index');
    }

    /**
     * Store a newly created resource in storage.
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
                'Role' => ['required', 'string', 'max:20'],
                'Employee' => ['required', 'string', 'max:20'],
                'BranchId' => ['required', 'integer', 'exists:t_Branches,Id'],
            ]);

        $role = Role::query()->where('id', $validated['Role'])->first();
        if (! $role instanceof Role) {
            throw ValidationException::withMessages(['Role' => 'invalid role defined']);
        }

        $branch = Branch::query()->where('Id', $validated['BranchId'])->first();
        if (! $branch instanceof Branch) {
            throw ValidationException::withMessages(['BranchId' => 'branch not found']);
        }

        // Only allow employees without a linked user AND whose email isn't already used by another user
        $employee = Employee::query()
            ->doesntHave('user')
            ->where('EmployeeNo', $validated['Employee'])
            ->whereNotNull('Email')
            ->whereNotIn('Email', function ($q) {
                $q->select('Email')->from('t_Users');
            })
            ->first();
        if (! $employee instanceof Employee) {
            throw ValidationException::withMessages(['Employee' => 'employee not found or already has an account/email in use.']);
        }

        // Use employee's existing branch
        $branch = $employee->branch;
        if (! $branch instanceof Branch) {
            throw ValidationException::withMessages(['Employee' => 'employee does not have a branch assigned.']);
        }

        $actor = $request->user();

        try {
            return DB::transaction(function () use ($actor, $employee, $branch) {
                UserService::create($employee, $actor)
                    ->welcomeEmail();

                return $this->succeeded('user added successfully');
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return $this->errored('This email already exists for another user.');
        } catch (Throwable | Exception $e) {
            Log::error('Error create user ' . $e->getMessage());
            Log::error($e);
        }

        return $this->errored('unexpected error, try again later');
    }

    public function create(): View
    {
        // Exclude employees with existing user accounts and those whose email is already present in users
        // Load branch relationship to display employee's branch
        $employees = Employee::query()
            ->with('branch')
            ->doesntHave('user')
            ->whereNotNull('Email')
            ->whereNotIn('Email', function ($q) {
                $q->select('Email')->from('t_Users');
            })
            ->get(['Id', 'EmployeeNo', 'FirstName', 'LastName', 'BranchID']);

        return view('settings.users.create')
            ->with('employees', $employees);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user): View
    {
        // If the user is viewing their own profile, show profile view
        if ($request->user()->UserID === $user->UserID) {
            return view('auth.profile')->with('user', $request->user());
        }

        // Eager load branchRoles with branch and role relationships
        $user->load(['branchRoles.branch', 'branchRoles.role']);

        $branches = Branch::all();
        $roles = Role::all();

        return view('settings.users.show', compact('user', 'branches', 'roles'));
    }

    public function edit(User $user): View
    {
        return view('settings.users.edit')
            ->with('user', $user)
            ->with('branches', Branch::all());
    }

    /**
     * Update the specified resource in storage.
     * @throws ValidationException
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        $phone = $request->getUserPhone($user);
        $email = $request->getUserEmail($user);
        $userID = $request->getUserID($user);
        $gender = $request->getGender();
        $branch = $request->getBranch();
        $clientID = $request->validated('ClientID');


        try {
            DB::transaction(static function () use ($branch, $user, $userID, $email, $gender, $request, $phone, $clientID) {
                (new UserService($user))
                    ->update($userID, $request->validated('Name'), $email, $phone, $gender, $request->user(), ($user->Email_Signature) ?? '', ($request->validated('Notes')) ?? '', $branch, $clientID);
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Exception $e) {
            Log::error('Error create user ' . $e->getMessage());

            return $this->errored('unexpected error, try again later');
        }

        return $this->succeeded('account updated successfully.', route('users.show', [$user->UserID]));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->UserID === $user->UserID) {
            return $this->errored("how, why you can't delete it? how did you get here");
        }

        try {
            (new UserService($user))->trash($request->user());
        } catch (ErroredException $e) {
            return $e->toJson();
        }

        return $this->succeeded('account trashed successfully.', route('users.index'));
    }
}
