<?php

namespace App\Services\HRM;

use App\Enums\Employee\GenderEnum;
use App\Models\BR\BranchDetails;
use App\Models\CrmBranch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EmployeeService
{
    public function __construct(public Employee $employee)
    {
    }

    public static function create(Department $department,CrmBranch $branch, User $actor,string $JobTitle,
        string $FirstName, string $Surname, string $Email, string $Phone, Carbon $JoinDate, GenderEnum $Gender,
        string $MiddleName=null, string $Address=null, Carbon $DateOfBirth=null,
    ): self
    {
        $employee = Employee::create([
            'EmployeeID' => self::_id(),
            'FirstName' => $FirstName,
            'LastName' => $Surname,
            'MiddleName' => $MiddleName,
            'Email' => $Email,
            'Phone' => $Phone,
            'Address'  => $Address,
            'DateOfBirth' => $DateOfBirth,
            'JoinDate' => $JoinDate ,
            'DepartmentId' => $department->Id,
            'BranchId' => $branch->Id,
            'JobTitle' => $JobTitle,
            'Gender' => $Gender->value,
            /*'MaritalStatus',*/
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        activity()->causedBy($actor)->performedOn($employee)->event('create')->log("Added employee {$employee->EmployeeID} .");
        return new self($employee);
    }


    private static function _id(): string
    {
        $number = Employee::query()->withTrashed()->count();
        do {
            $number++;
            $slug = Str::slug('E' . Str::padLeft(($number), 5, '0'));
        } while (Employee::query()->where('EmployeeID', $slug)->withTrashed()->exists());

        return $slug;
    }
}
