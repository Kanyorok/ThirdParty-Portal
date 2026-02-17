<?php

namespace App\Http\Controllers\HRM;

use App\Enums\Employee\GenderEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\HRM\AddEmployeeRequest;
use App\Http\Requests\HRM\EmployeePersonalRequest;
use App\Models\Core\Branch;
use App\Models\HR\Employee;
use App\Models\HRM\Department;
use App\Services\HRM\EmployeeService;
use App\Traits\Controller\EmployeeTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class EmployeeController extends Controller
{
    use EmployeeTrait;

    public function __construct()
    {
        $this->middleware('ajax')->except(['index', 'create', 'show']);
        $this->authorizeResource(Employee::class);
    }

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->getEmployees(Employee::query()->select('*'), with: ['department', 'photo']);
        }

        return view('hrms.employee.index');
    }

    public function create(): View
    {
        return view('hrms.employee.create')
            ->with('departments', Department::query()->get(['Name', 'DepartmentID']))
            ->with('branches', Branch::query()->get(['Name', 'BranchID']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddEmployeeRequest $request)
    {
        $image = $request->getImage();
        $joinDate = $request->getJoinDate();
        $dob = $request->getDateOfBirth();
        $gender = $request->getGender();
        $branch = $request->getBranch();
        $department = $request->getDepartment();
        $actor = $request->user();
        $phone = $request->getPhoneNumber();
        $email = $request->getEmail();

        try {
            return DB::transaction(function () use ($request, $image, $joinDate, $dob, $gender, $branch, $department, $actor, $phone, $email) {
                $employee = EmployeeService::create(
                    department: $department,
                    branch: $branch,
                    actor: $actor,
                    JobTitle: $request->string('JobTitle')->trim()->toString(),
                    FirstName: $request->string('FirstName')->trim()->toString(),
                    Surname: $request->string('LastName')->trim()->toString(),
                    Email: $email,
                    Phone: $phone,
                    JoinDate: $joinDate,
                    Gender: $gender,
                    MiddleName: $request->string('MiddleName')->trim()->toString(),
                    Address: $request->string('Address')->trim()->toString(),
                    DateOfBirth: $dob
                );
                if ($image instanceof UploadedFile) {
                    $employee->setImage($image, $actor);
                }
                if ($request->addUser()) {
                    $employee->createUser($actor)->welcomeEmail();
                }

                return $this->succeeded($employee->employee->EmployeeID . ' created successfully.', route('employees.show', [$employee->employee->EmployeeID]));
            });
        } catch (Throwable | Exception $e) {
            Log::error("--- CREATE EMPLOYEE ERROR --- " . $e->getMessage());
            Log::error($e);
        }

        return $this->errored('create employee failed.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): View
    {
        return view('hrms.employee.show')
            ->with('employee', $employee->load(['department', 'branch']));
    }

    /**
     * Edit Employee Employment details
     */
    public function edit(Employee $employee)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeePersonalRequest $request, Employee $employee)
    {
        $dob = $request->getDateOfBirth();
        $actor = $request->user();

        try {
            return DB::transaction(function () use ($actor, $dob, $request, $employee) {
                $employee->update([
                    'FirstName' => $request->string('FirstName')->trim()->toString(),
                    'Surname' => $request->string('LastName')->trim()->toString(),
                    'MiddleName' => $request->string('MiddleName')->trim()->toString(),
                    'Email' => $request->string('Email')->trim()->toString(),
                    'Phone' => $request->string('Phone')->trim()->toString(),
                    'Address' => $request->string('Address')->trim()->toString(),
                    'Gender' => $request->enum('Gender', GenderEnum::class)->value,
                    'DateOfBirth' => $dob,
                    'ModifiedBy' => $actor->Id,
                ]);
                activity()->causedBy($actor)->performedOn($employee)->event('update')->log('updated employee ' . $employee->EmployeeID);

                return $this->succeeded('employee updated successfully.', route: route('employees.show', $employee->EmployeeID));
            });
        } catch (Throwable | Exception $e) {
            Log::error("--- UPDATE EMPLOYEE ERROR --- " . $e->getMessage());
            Log::error($e);
        }

        return $this->errored('update employee failed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
    }
}
