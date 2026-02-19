<?php

namespace App\Imports\HR;

use App\Models\HR\Employee;
use App\Models\HR\EmployeeSalaryHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class BulkSalaryImport implements OnEachRow, WithHeadingRow
{
    use ImportHelper;

    private int $processed = 0;
    private int $updated = 0;
    private int $skipped = 0;

    public function onRow(Row $row): void
    {
        $this->processed++;

        $data = $row->toArray();
        $data = array_change_key_case($data, CASE_LOWER);
        $data = array_map([$this, 'cleanValue'], $data);

        $employeeNo = $data['employeeno'] ?? $data['employee_no'] ?? null;
        $basicSalary = $data['basicsalary'] ?? $data['basic_salary'] ?? null;

        if (! $employeeNo || $basicSalary === null) {
            $this->skipped++;

            return;
        }

        $employee = Employee::where('EmployeeNo', $employeeNo)->first();
        if (! $employee) {
            $this->skipped++;
            Log::warning('Salary import skipped: employee not found.', ['employee_no' => $employeeNo]);

            return;
        }

        $newSalary = (float)$basicSalary;
        if ((float)$employee->BasicSalary === $newSalary) {
            $this->skipped++;

            return;
        }

        $userId = Auth::id() ?? 1;
        $employee->update([
            'BasicSalary' => $newSalary,
            'ModifiedBy' => $userId,
            'ModifiedOn' => now(),
        ]);

        $effectiveFrom = $this->parseDate($data['effectivefrom'] ?? $data['effective_from'] ?? $data['salaryeffectivefrom'] ?? $data['salary_effective_from'] ?? null)
            ?: now()->toDateString();
        EmployeeSalaryHistory::create([
            'EmployeeID' => $employee->Id,
            'BasicSalary' => $newSalary,
            'EffectiveFrom' => $effectiveFrom,
            'Notes' => $data['notes'] ?? 'Bulk salary update',
            'CreatedBy' => $userId,
            'CreatedOn' => now(),
        ]);

        $this->updated++;
    }

    public function getProcessedCount(): int
    {
        return $this->processed;
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
