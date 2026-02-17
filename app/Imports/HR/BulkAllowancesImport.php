<?php

namespace App\Imports\HR;

use App\Models\HR\Employee;
use App\Models\HR\MonthlyAllowance;
use App\Models\HR\PayrollAllowance;
use App\Models\HR\PayrollAllowanceRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class BulkAllowancesImport implements OnEachRow, WithHeadingRow
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
        $allowanceCode = $data['allowancecode'] ?? $data['allowance_code'] ?? null;
        $allowanceName = $data['allowancename'] ?? $data['allowance_name'] ?? null;

        if (!$employeeNo || (!$allowanceCode && !$allowanceName)) {
            $this->skipped++;
            return;
        }

        $employee = Employee::where('EmployeeNo', $employeeNo)->first();
        if (!$employee) {
            $this->skipped++;
            Log::warning('Allowance import skipped: employee not found.', ['employee_no' => $employeeNo]);
            return;
        }

        $allowance = PayrollAllowance::where('IsActive', 1)
            ->when($allowanceCode, function ($q) use ($allowanceCode) {
                $q->where('Code', $allowanceCode);
            })
            ->when(!$allowanceCode && $allowanceName, function ($q) use ($allowanceName) {
                $q->where('Name', $allowanceName);
            })
            ->first();

        if (!$allowance) {
            $this->skipped++;
            Log::warning('Allowance import skipped: allowance not found.', [
                'employee_no' => $employeeNo,
                'allowance' => $allowanceCode ?: $allowanceName,
            ]);
            return;
        }

        if ($allowance->IsMandatory) {
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
        if ($amount === null) {
            $amount = $this->calculateAllowanceAmount($allowance, (float)$employee->BasicSalary);
        }

        $isTaxable = $this->parseBoolean($data['istaxable'] ?? $data['is_taxable'] ?? null);
        if ($isTaxable === null) {
            $isTaxable = (bool)$allowance->IsTaxable;
        }

        $isRecurring = $this->parseBoolean($data['isrecurring'] ?? $data['is_recurring'] ?? null);
        if ($isRecurring === null) {
            $isRecurring = false;
        }

        $status = $data['status'] ?? 'Pending';
        $userId = Auth::id() ?? 1;

        $existing = MonthlyAllowance::where('EmployeeID', $employee->Id)
            ->where('AllowanceID', $allowance->Id)
            ->where('Month', $month)
            ->where('Year', $year)
            ->first();

        $payload = [
            'EmployeeID' => $employee->Id,
            'AllowanceID' => $allowance->Id,
            'Name' => $allowance->Name,
            'Amount' => (float)$amount,
            'Month' => $month,
            'Year' => $year,
            'IsTaxable' => $isTaxable,
            'IsRecurring' => $isRecurring,
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
        MonthlyAllowance::create($payload);
        $this->created++;
    }

    private function calculateAllowanceAmount(PayrollAllowance $allowance, float $basicSalary): float
    {
        $rule = PayrollAllowanceRule::where('AllowanceID', $allowance->Id)
            ->where('IsActive', 1)
            ->orderByDesc('EffectiveFrom')
            ->first();
        if (!$rule) {
            return 0.0;
        }

        $amount = 0.0;
        switch ($rule->CalcMethod) {
            case 'PercentageOnBasic':
            case 'PercentageOnGross':
            case 'PercentageOnBand':
                $amount = $basicSalary * ((float)($rule->Rate ?? 0) / 100);
                break;
            case 'Flat':
            case 'FlatOnBand':
                $amount = (float)($rule->Amount ?? 0);
                break;
            case 'PercentageOfActingReference':
                $amount = 0.0;
                break;
            default:
                $amount = 0.0;
        }

        if ($rule->MinAmount !== null) {
            $amount = max($amount, (float)$rule->MinAmount);
        }
        if ($rule->MaxAmount !== null) {
            $amount = min($amount, (float)$rule->MaxAmount);
        }

        return round(max(0, $amount), 2);
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
