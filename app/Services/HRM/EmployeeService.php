<?php

namespace App\Services\HRM;

use App\Enums\Employee\GenderEnum;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\HR\Employee;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class EmployeeService
{
    public function __construct(public Employee $employee)
    {
    }

    public static function create(
        Department $department,
        Branch $branch,
        User $actor,
        string $JobTitle,
        string $FirstName,
        string $Surname,
        string $Email,
        string $Phone,
        Carbon $JoinDate,
        GenderEnum $Gender,
        string $MiddleName = null,
        string $Address = null,
        Carbon $DateOfBirth = null,
    ): self {
        $employee = Employee::create([
            'EmployeeNo' => self::_id(),
            'FirstName' => $FirstName,
            'LastName' => $Surname,
            'MiddleName' => $MiddleName,
            'Email' => $Email,
            'Phone' => $Phone,
            'Address' => $Address,
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

        activity()->causedBy($actor)->performedOn($employee)->event('create')->log("Added employee {$employee->EmployeeNo}.");
        return new self($employee);
    }

    public function createUser(User $actor): UserService
    {
        return UserService::create($this->employee, $actor);
    }

    public function setImage(UploadedFile $file, User $actor): static
    {
        $this->employee->setImage($file, $actor, 'ImageId');

        activity()->causedBy($actor)->performedOn($this->employee)->event('update')->log("Updated employee Image {$this->employee->EmployeeNo}.");
        return $this;
    }

    private static function _id(): string
    {
        $number = Employee::query()->withTrashed()->count();
        do {
            $number++;
            $slug = 'E' . Str::padLeft(($number), 5, '0');
        } while (Employee::query()->where('EmployeeNo', $slug)->withTrashed()->exists());

        return $slug;
    }
}
