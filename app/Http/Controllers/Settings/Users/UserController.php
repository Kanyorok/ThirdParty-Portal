<?php

namespace App\Http\Controllers\Settings\Users;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserRequest;
use App\Models\CrmBranch;
use App\Models\Employee;
use App\Models\User;
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
            'Employee' => ['required', 'string', 'max:20']
        ]);

        $role = Role::query()->where('id', $validated['Role'])->first();
        if (!$role instanceof Role) {
            throw ValidationException::withMessages(['Role' => 'invalid role defined']);
        }

        $employee = Employee::query()->doesntHave('user')->where('EmployeeID', $validated['Employee'])->first();
        if (!$employee instanceof Employee) {
            throw ValidationException::withMessages(['Employee' => 'employee not found.']);
        }

        $actor = $request->user();
        try {
            return DB::transaction(function () use ($actor, $role, $employee) {
                UserService::create($employee, $actor)
                    ->setRole($role, $actor)
                    ->welcomeEmail();
                return $this->succeeded('user added successfully');
            });
        } catch (ErroredException $e) {
            return $e->toJson();
        } catch (Throwable|Exception $e) {
            Log::error('Error create user ' . $e->getMessage());
            Log::error($e);
        }

        return $this->errored('unexpected error, try again later');
    }

    public function create(): View
    {
        return view('settings.users.create')
            ->with('employees', Employee::doesntHave('user')->get(['EmployeeID', 'FirstName', 'LastName']))
            ->with('Roles', Role::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $user): View
    {
        if ($request->user()->UserID === $user->UserID) {
            return view('auth.profile')->with('user', $request->user());
        }

        return view('settings.users.show', compact('user'));
    }


    public function edit(User $user): View
    {
        return view('settings.users.edit')
            ->with('user', $user)
            ->with('branches', CrmBranch::all());
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

        (new UserService($user))->trash($request->user());

        return $this->succeeded('account trashed successfully.', route('settings.users'));
    }
}
