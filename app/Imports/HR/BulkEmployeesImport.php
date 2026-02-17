<?php

namespace App\Imports\HR;

use App\Models\Core\Branch;
use App\Models\Finance\Bank;
use App\Models\Finance\BankBranch;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeSalaryHistory;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HRM\Department;
use App\Services\HR\PayrollMandatoryAllocator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class BulkEmployeesImport implements OnEachRow, WithHeadingRow
{
    use ImportHelper;

    private int $processed = 0;
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    public function onRow(Row $row): void
    {
        $this->processed++;

        $data = $row->toArray();
        $data = array_change_key_case($data, CASE_LOWER);
        $data = array_map([$this, 'cleanValue'], $data);

        $employeeNo = $data['employeeno'] ?? $data['employee_no'] ?? null;
        $firstName = $data['firstname'] ?? $data['first_name'] ?? null;
        $lastName = $data['lastname'] ?? $data['last_name'] ?? null;
        $basicSalary = $data['basicsalary'] ?? $data['basic_salary'] ?? null;

        $branchValue = $data['branch'] ?? $data['branchid'] ?? $data['branch_id'] ?? null;
        $departmentValue = $data['department'] ?? $data['departmentid'] ?? $data['department_id'] ?? null;

        $branchId = $this->resolveBranchId($branchValue);
        $departmentId = $this->resolveDepartmentId($departmentValue);

        if (! $employeeNo || ! $firstName || ! $lastName || $basicSalary === null || ! $branchId || ! $departmentId) {
            $this->skipped++;
            Log::warning('Employee import skipped due to missing required fields.', [
                'employee_no' => $employeeNo,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

            return;
        }

        $gradeId = $this->resolveGradeId($data['grade'] ?? $data['gradeid'] ?? $data['grade_id'] ?? null);
        $roleId = $this->resolveRoleId(
            $data['role'] ?? $data['roleid'] ?? $data['role_id'] ?? null,
            $departmentId,
            $gradeId
        );
        $supervisorId = $this->resolveSupervisorId($data['supervisor'] ?? $data['supervisorid'] ?? $data['supervisor_id'] ?? null);

        $bankId = $this->resolveBankId($data['bank'] ?? $data['bankid'] ?? $data['bank_id'] ?? null);
        $bankBranchId = $this->resolveBankBranchId($data['bankbranch'] ?? $data['bank_branch'] ?? $data['bankbranchid'] ?? $data['bankbranch_id'] ?? null, $bankId);
        if ($bankBranchId && ! $bankId) {
            $bankId = BankBranch::where('BranchID', $bankBranchId)->value('BankID');
        }

        $status = $data['status'] ?? null;
        $isActive = $this->parseBoolean($data['isactive'] ?? $data['is_active'] ?? null);
        if ($isActive === null && $status) {
            $isActive = in_array($status, ['Active', 'Pending', 'OnHold'], true);
        }

        $paymentMode = $data['paymentmode'] ?? $data['payment_mode'] ?? null;
        if (! $paymentMode) {
            $paymentMode = ($data['bankaccount'] ?? $data['bank_account'] ?? null) ? 'Bank' : 'Cash';
        }

        $payload = [
            'EmployeeNo' => $employeeNo,
            'FirstName' => $firstName,
            'LastName' => $lastName,
            'OtherNames' => $data['othernames'] ?? $data['other_names'] ?? null,
            'Email' => $data['email'] ?? null,
            'Phone' => $data['phone'] ?? null,
            'Gender' => $this->normalizeGender($data['gender'] ?? null),
            'DateOfBirth' => $this->parseDate($data['dateofbirth'] ?? $data['date_of_birth'] ?? null),
            'Address' => $data['address'] ?? null,
            'BranchID' => $branchId,
            'DepartmentID' => $departmentId,
            'GradeID' => $gradeId,
            'RoleID' => $roleId,
            'SupervisorID' => $supervisorId,
            'EmploymentDate' => $this->parseDate($data['employmentdate'] ?? $data['employment_date'] ?? null),
            'EmploymentType' => $data['employmenttype'] ?? $data['employment_type'] ?? null,
            'ContractType' => $data['contracttype'] ?? $data['contract_type'] ?? null,
            'NSSFNo' => $data['nssfno'] ?? $data['nssf_no'] ?? null,
            'NHIFNo' => $data['nhifno'] ?? $data['nhif_no'] ?? null,
            'KRAPIN' => $data['krapin'] ?? $data['kra_pin'] ?? null,
            'BasicSalary' => (float)$basicSalary,
            'PaymentMode' => $paymentMode,
            'BankID' => $bankId,
            'BankBranchID' => $bankBranchId,
            'BankAccount' => $data['bankaccount'] ?? $data['bank_account'] ?? null,
            'Status' => $status,
            'StatusReason' => $data['statusreason'] ?? $data['status_reason'] ?? null,
            'IsActive' => $isActive === null ? null : (int)$isActive,
        ];

        $userId = Auth::id() ?? 1;
        $employee = Employee::where('EmployeeNo', $employeeNo)->first();
        if ($employee) {
            if ($employee->Status === 'Exited') {
                $this->skipped++;

                return;
            }
            $updates = $this->buildUpdatePayload($payload);
            $statusChanged = $status && $status !== $employee->Status;
            if ($statusChanged) {
                $updates['StatusChangedBy'] = $userId;
                $updates['StatusChangedOn'] = now();
                if ($isActive === null) {
                    $updates['IsActive'] = in_array($status, ['Active', 'Pending', 'OnHold'], true) ? 1 : 0;
                }
            }
            $updates['ModifiedBy'] = $userId;
            $updates['ModifiedOn'] = now();

            $salaryChanged = array_key_exists('BasicSalary', $updates) && (float)$updates['BasicSalary'] !== (float)$employee->BasicSalary;
            $employee->update($updates);
            if ($salaryChanged) {
                $effectiveFrom = $this->parseDate($data['salaryeffectivefrom'] ?? $data['salary_effective_from'] ?? null)
                    ?: now()->toDateString();
                EmployeeSalaryHistory::create([
                    'EmployeeID' => $employee->Id,
                    'BasicSalary' => (float)$updates['BasicSalary'],
                    'EffectiveFrom' => $effectiveFrom,
                    'Notes' => 'Bulk salary update',
                    'CreatedBy' => $userId,
                    'CreatedOn' => now(),
                ]);
            }

            $this->updated++;

            return;
        }

        if (! $payload['Status']) {
            $payload['Status'] = 'Pending';
        }
        if ($payload['IsActive'] === null) {
            $payload['IsActive'] = in_array($payload['Status'], ['Active', 'Pending', 'OnHold'], true) ? 1 : 0;
        }
        $payload['CreatedBy'] = $userId;
        $payload['CreatedOn'] = now();
        $payload['StatusChangedBy'] = $payload['Status'] ? $userId : null;
        $payload['StatusChangedOn'] = $payload['Status'] ? now() : null;

        $employee = Employee::create($payload);
        $effectiveFrom = $this->parseDate($data['salaryeffectivefrom'] ?? $data['salary_effective_from'] ?? null)
            ?: ($employee->EmploymentDate ? $employee->EmploymentDate->format('Y-m-d') : now()->toDateString());
        EmployeeSalaryHistory::create([
            'EmployeeID' => $employee->Id,
            'BasicSalary' => $employee->BasicSalary,
            'EffectiveFrom' => $effectiveFrom,
            'Notes' => 'Initial salary',
            'CreatedBy' => $userId,
            'CreatedOn' => now(),
        ]);

        app(PayrollMandatoryAllocator::class)->syncForEmployee($employee, now()->month, now()->year);
        $this->created++;
    }

    private function buildUpdatePayload(array $payload): array
    {
        $updates = [];
        foreach ($payload as $key => $value) {
            if ($value !== null) {
                $updates[$key] = $value;
            }
        }

        return $updates;
    }

    private function resolveBranchId($value): ?int
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            $branch = Branch::whereNull('DeletedOn')
                ->where(function ($q) use ($value) {
                    $q->where('Id', (int)$value)->orWhere('BranchID', (string)$value);
                })
                ->first();

            return $branch?->Id;
        }

        $branch = Branch::whereNull('DeletedOn')
            ->where(function ($q) use ($value) {
                $q->where('Name', $value)->orWhere('BranchID', $value);
            })
            ->first();

        return $branch?->Id;
    }

    private function resolveDepartmentId($value): ?int
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }
        if (is_numeric($value)) {
            $dept = Department::whereNull('DeletedOn')
                ->where(function ($q) use ($value) {
                    $q->where('Id', (int)$value)->orWhere('DepartmentID', (string)$value);
                })
                ->first();

            return $dept?->Id;
        }

        $dept = Department::whereNull('DeletedOn')
            ->where(function ($q) use ($value) {
                $q->where('Name', $value)->orWhere('DepartmentID', $value);
            })
            ->first();

        return $dept?->Id;
    }

    private function resolveGradeId($value): ?int
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }
        $query = JobGrade::where('IsActive', 1);
        if (is_numeric($value)) {
            $query->where('Id', (int)$value);
        } else {
            $query->where(function ($q) use ($value) {
                $q->where('Code', $value)->orWhere('Name', $value);
            });
        }

        return $query->value('Id');
    }

    private function resolveRoleId($value, ?int $departmentId, ?int $gradeId): ?int
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }

        $query = JobRole::where('IsActive', 1);
        if ($departmentId) {
            $query->where('DepartmentID', $departmentId);
        }
        if ($gradeId) {
            $query->where('GradeID', $gradeId);
        }

        if (is_numeric($value)) {
            $query->where('Id', (int)$value);
        } else {
            $query->where(function ($q) use ($value) {
                $q->where('Code', $value)->orWhere('Name', $value);
            });
        }

        return $query->value('Id');
    }

    private function resolveSupervisorId($value): ?int
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }
        $query = Employee::query();
        $employee = Employee::where('EmployeeNo', $value)->first();
        if ($employee) {
            return $employee->Id;
        }

        if (is_numeric($value)) {
            $employee = $query->where('Id', (int)$value)->first();
            if ($employee) {
                return $employee->Id;
            }
        }

        if (str_contains((string)$value, '@')) {
            return Employee::where('Email', $value)->value('Id');
        }

        $parts = preg_split('/\\s+/', (string)$value, 2);
        if (count($parts) === 2) {
            $employee = Employee::where('FirstName', $parts[0])
                ->where('LastName', $parts[1])
                ->first();

            return $employee?->Id;
        }

        return null;
    }

    private function resolveBankId($value): ?int
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }
        $query = Bank::where('IsActive', 1);
        if (is_numeric($value)) {
            $query->where('BankID', (int)$value);
        } else {
            $query->where(function ($q) use ($value) {
                $q->where('BankName', $value)
                    ->orWhere('ShortName', $value)
                    ->orWhere('BankCode', $value);
            });
        }

        return $query->value('BankID');
    }

    private function resolveBankBranchId($value, ?int $bankId): ?int
    {
        $value = $this->cleanValue($value);
        if ($value === null) {
            return null;
        }
        $query = BankBranch::where('IsActive', 1);
        if ($bankId) {
            $query->where('BankID', $bankId);
        }
        if (is_numeric($value)) {
            $query->where('BranchID', (int)$value);
        } else {
            $query->where(function ($q) use ($value) {
                $q->where('BranchName', $value)
                    ->orWhere('BranchCode', $value);
            });
        }

        return $query->value('BranchID');
    }

    public function getProcessedCount(): int
    {
        return $this->processed;
    }

    public function getCreatedCount(): int
    {
        return $this->created;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}
