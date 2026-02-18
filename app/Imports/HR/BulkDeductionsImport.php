<?php

namespace App\Imports\HR;

use App\Models\HR\Employee;
use App\Models\HR\MonthlyDeduction;
use App\Models\HR\PayrollDeduction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class BulkDeductionsImport implements OnEachRow, WithHeadingRow
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
        $deductionCode = $data['deductioncode'] ?? $data['deduction_code'] ?? null;
        $deductionName = $data['deductionname'] ?? $data['deduction_name'] ?? null;

        if (! $employeeNo || (! $deductionCode && ! $deductionName)) {
            $this->skipped++;

            return;
        }

        $employee = Employee::where('EmployeeNo', $employeeNo)->first();
        if (! $employee) {
            $this->skipped++;
            Log::warning('Deduction import skipped: employee not found.', ['employee_no' => $employeeNo]);

            return;
        }

        $deduction = PayrollDeduction::where('IsActive', 1)
            ->when($deductionCode, function ($q) use ($deductionCode) {
                $q->where('Code', $deductionCode);
            })
            ->when(! $deductionCode && $deductionName, function ($q) use ($deductionName) {
                $q->where('Name', $deductionName);
            })
            ->first();

        if (! $deduction) {
            $this->skipped++;
            Log::warning('Deduction import skipped: deduction not found.', [
                'employee_no' => $employeeNo,
                'deduction' => $deductionCode ?: $deductionName,
            ]);

            return;
        }

        if ($deduction->IsMandatory) {
            $this->skipped++;

            return;
        }

        $month = (int)($data['month'] ?? now()->month);
        $year = (int)($data['year'] ?? now()->year);
        if ($month < 1 || $month > 12 || $year < 2000) {
            $this->skipped++;

            return;
        }

        $amount = $data['amount'] ?? null;
        $autoCalculated = $amount === null;
        $amount = $amount === null ? 0 : (float)$amount;

        $isRecurring = $this->parseBoolean($data['isrecurring'] ?? $data['is_recurring'] ?? null);
        if ($isRecurring === null) {
            $isRecurring = false;
        }

        $status = $data['status'] ?? 'Pending';
        $userId = Auth::id() ?? 1;

        $existing = MonthlyDeduction::where('EmployeeID', $employee->Id)
            ->where('DeductionID', $deduction->Id)
            ->where('Month', $month)
            ->where('Year', $year)
            ->first();

        $payload = [
            'EmployeeID' => $employee->Id,
            'DeductionID' => $deduction->Id,
            'Name' => $deduction->Name,
            'Amount' => $amount,
            'Month' => $month,
            'Year' => $year,
            'IsRecurring' => $isRecurring,
            'IsAutoCalculated' => $autoCalculated,
            'Status' => $status,
        ];

        if ($existing) {
            $payload['ModifiedBy'] = $userId;
            $payload['ModifiedOn'] = now();
            if ($status === 'Approved') {
                $payload['ApprovedBy'] = $userId;
                $payload['ApprovedOn'] = now();
            }
            $existing->update($payload);
            $this->updated++;

            return;
        }

        $payload['CreatedBy'] = $userId;
        $payload['CreatedOn'] = now();
        if ($status === 'Approved') {
            $payload['ApprovedBy'] = $userId;
            $payload['ApprovedOn'] = now();
        }
        MonthlyDeduction::create($payload);
        $this->created++;
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
