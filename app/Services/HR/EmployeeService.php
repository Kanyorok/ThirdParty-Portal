<?php

namespace App\Services\HR;

use App\Models\Auth\User;
use App\Models\HR\Employee;

class EmployeeService
{
    public function __construct(public Employee $employee)
    {
    }

    /**
     * Generate the next Employee Number
     * Format: E00001, E00002, etc.
     */
    public static function generateEmployeeNo(): string
    {
        // Get the count of all employees (including soft-deleted ones via DeletedOn check)
        $count = Employee::count();

        do {
            $count++;
            $employeeNo = 'E' . str_pad($count, 5, '0', STR_PAD_LEFT);
        } while (Employee::where('EmployeeNo', $employeeNo)->exists());

        return $employeeNo;
    }

    /**
     * Create a new employee with all required fields
     */
    public static function create(array $data, User $actor): self
    {
        // Generate EmployeeNo if not provided
        if (empty($data['EmployeeNo'])) {
            $data['EmployeeNo'] = self::generateEmployeeNo();
        }

        // Set audit fields
        $data['CreatedBy'] = $actor->Id;
        $data['CreatedOn'] = now();
        $data['IsActive'] = $data['IsActive'] ?? 1;
        $data['Status'] = $data['Status'] ?? 'Active';

        $employee = Employee::create($data);

        activity()
            ->causedBy($actor)
            ->performedOn($employee)
            ->event('create')
            ->log("Created employee {$employee->EmployeeNo}");

        return new self($employee);
    }

    /**
     * Create a user account for this employee
     */
    public function createUserAccount(User $actor): UserService
    {
        return UserService::createForEmployee($this->employee, $actor);
    }

    /**
     * Update employee information
     */
    public function update(array $data, User $actor): self
    {
        $data['ModifiedBy'] = $actor->Id;
        $data['ModifiedOn'] = now();

        $this->employee->update($data);

        activity()
            ->causedBy($actor)
            ->performedOn($this->employee)
            ->event('update')
            ->log("Updated employee {$this->employee->EmployeeNo}");

        return $this;
    }

    /**
     * Soft delete an employee
     */
    public function delete(User $actor): bool
    {
        $this->employee->DeletedBy = $actor->Id;
        $this->employee->DeletedOn = now();
        $this->employee->IsActive = 0;
        $this->employee->Status = 'Exited';
        $this->employee->save();

        activity()
            ->causedBy($actor)
            ->performedOn($this->employee)
            ->event('delete')
            ->log("Deleted employee {$this->employee->EmployeeNo}");

        return true;
    }

    /**
     * Check if employee has a user account
     */
    public function hasUserAccount(): bool
    {
        return User::where('EmployeeId', $this->employee->Id)
            ->whereNull('DeletedOn')
            ->exists();
    }

    /**
     * Get the user account for this employee
     */
    public function getUserAccount(): ?User
    {
        return User::where('EmployeeId', $this->employee->Id)
            ->whereNull('DeletedOn')
            ->first();
    }
}
