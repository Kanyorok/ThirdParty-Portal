<?php

namespace App\Services\HR;

use App\Models\HR\Employee;
use App\Models\HR\MonthlyAllowance;
use App\Models\HR\MonthlyDeduction;
use App\Models\HR\PayrollAllowance;
use App\Models\HR\PayrollDeduction;

class PayrollMandatoryAllocator
{
    public function syncForEmployee(Employee $employee, int $month, int $year): void
    {
        if ((int)($employee->IsActive ?? 1) !== 1 || $employee->DeletedOn !== null || (string)($employee->Status ?? '') === 'Exited') {
            return;
        }
        $this->syncMandatoryAllowancesForEmployee($employee, $month, $year);
        $this->syncMandatoryDeductionsForEmployee($employee, $month, $year);
    }

    public function syncForAllEmployees(int $month, int $year): void
    {
        Employee::where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->where(function ($q) {
                $q->whereNull('Status')->orWhere('Status', '!=', 'Exited');
            })
            ->chunk(200, function ($employees) use ($month, $year) {
            foreach ($employees as $employee) {
                $this->syncForEmployee($employee, $month, $year);
            }
        });
    }

    private function syncMandatoryAllowancesForEmployee(Employee $employee, int $month, int $year): void
    {
        $gradeId = $employee->GradeID;
        $mandatory = PayrollAllowance::where('IsActive', 1)
            ->where('IsMandatory', 1)
            ->where(function ($q) use ($gradeId) {
                $q->whereDoesntHave('grades');
                if ($gradeId) {
                    $q->orWhereHas('grades', function ($g) use ($gradeId) {
                        $g->where('t_HRJobGrades.Id', $gradeId);
                    });
                }
            })
            ->get();

        foreach ($mandatory as $allowance) {
            $existing = MonthlyAllowance::where('EmployeeID', $employee->Id)
                ->where('AllowanceID', $allowance->Id)
                ->where('Month', $month)
                ->where('Year', $year)
                ->first();

            $defaultAmount = $this->calculateAllowanceAmount($allowance, (float)($employee->BasicSalary ?? 0));
            if ($existing) {
                if ((float)$existing->Amount <= 0 && $defaultAmount > 0) {
                    $existing->update([
                        'Amount' => $defaultAmount,
                        'ModifiedBy' => auth()->id(),
                        'ModifiedOn' => now(),
                    ]);
                }
                continue;
            }

            MonthlyAllowance::create([
                'EmployeeID' => $employee->Id,
                'AllowanceID' => $allowance->Id,
                'Name' => $allowance->Name,
                'Amount' => $defaultAmount,
                'Month' => $month,
                'Year' => $year,
                'IsTaxable' => $allowance->IsTaxable,
                'IsRecurring' => true,
                'Status' => 'Approved',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ApprovedBy' => auth()->id(),
                'ApprovedOn' => now(),
            ]);
        }
    }

    private function syncMandatoryDeductionsForEmployee(Employee $employee, int $month, int $year): void
    {
        $mandatory = PayrollDeduction::where('IsActive', 1)->where('IsMandatory', 1)->get();
        foreach ($mandatory as $deduction) {
            $existing = MonthlyDeduction::where('EmployeeID', $employee->Id)
                ->where('DeductionID', $deduction->Id)
                ->where('Month', $month)
                ->where('Year', $year)
                ->orderByDesc('Id')
                ->first();
            if ($existing) {
                if ($existing->Status !== 'Approved' || !$existing->IsAutoCalculated) {
                    $existing->update([
                        'Amount' => (float)($existing->Amount ?? 0),
                        'IsRecurring' => true,
                        'IsAutoCalculated' => true,
                        'Status' => 'Approved',
                        'ApprovedBy' => auth()->id(),
                        'ApprovedOn' => now(),
                        'ModifiedBy' => auth()->id(),
                        'ModifiedOn' => now(),
                    ]);
                }
                continue;
            }
            MonthlyDeduction::create([
                'EmployeeID' => $employee->Id,
                'DeductionID' => $deduction->Id,
                'Name' => $deduction->Name,
                'Amount' => 0,
                'IsRecurring' => true,
                'IsAutoCalculated' => true,
                'Month' => $month,
                'Year' => $year,
                'Status' => 'Approved',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
                'ApprovedBy' => auth()->id(),
                'ApprovedOn' => now(),
            ]);
        }
    }

    private function calculateAllowanceAmount(PayrollAllowance $allowance, float $basicSalary): float
    {
        $rule = $allowance->rules()
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
                $amount = $basicSalary * ((float)($rule->Rate ?? 0) / 100);
                break;
            case 'Flat':
                $amount = (float)($rule->Amount ?? 0);
                break;
            case 'PercentageOnBand':
                $amount = $basicSalary * ((float)($rule->Rate ?? 0) / 100);
                break;
            case 'FlatOnBand':
                $amount = (float)($rule->Amount ?? 0);
                break;
            case 'PercentageOfActingReference':
                // Not used for mandatory allocation.
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
}
