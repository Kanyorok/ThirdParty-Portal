<?php

namespace App\Services\HR;

use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceJournalLines;
use App\Models\HR\Employee;
use App\Models\HR\GratuityAccrual;
use App\Models\HR\MonthlyAllowance;
use App\Models\HR\MonthlyDeduction;
use App\Models\HR\PayrollAllowance;
use App\Models\HR\PayrollDeduction;
use App\Models\HR\PayrollEmployerContribution;
use App\Models\HR\PayrollGLSetting;
use App\Models\HR\PayrollRun;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PayrollFinancePostingService
{
    /**
     * Posting modes:
     * - summary: consolidated (no branch/department)
     * - branch_department: split by employee branch + department
     */
    public function postRun(PayrollRun $run, string $postingMode = 'summary'): FinanceJournalEntry
    {
        $postingMode = strtolower(trim($postingMode));
        if (! in_array($postingMode, ['summary', 'branch_department'], true)) {
            throw ValidationException::withMessages([
                'posting_mode' => 'Invalid posting mode selected.',
            ]);
        }

        $cycle = $run->cycle;
        if (! $cycle) {
            throw ValidationException::withMessages([
                'posting_mode' => 'Payroll run has no cycle attached.',
            ]);
        }

        $settings = PayrollGLSetting::orderByDesc('Id')->first();
        if (! $settings || ! $settings->PayrollControlGLAccountID || ! $settings->BasicSalaryExpenseGLAccountID) {
            throw ValidationException::withMessages([
                'posting_mode' => 'Configure Payroll GL Setup first (Payroll Control and Basic Salary Expense).',
            ]);
        }

        $idempotencyKey = "HR_PAYROLL_RUN_{$run->Id}_" . strtoupper($postingMode);
        $existing = FinanceJournalEntry::where('IdempotencyKey', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        $periodStart = Carbon::create((int)$cycle->Year, (int)$cycle->Month, 1)->startOfMonth();
        $journalDate = $periodStart->copy()->endOfMonth()->toDateString();

        $lines = collect();

        // Base Salary (DR Basic Salary Expense, CR Payroll Control)
        $lines = $lines->merge($this->buildBaseSalaryLines($run, $settings, $postingMode));

        // Allowances (DR Allowance Expense, CR Payroll Control [default] or configured)
        $lines = $lines->merge($this->buildAllowanceLines($cycle->Month, $cycle->Year, $settings, $postingMode));

        // Deductions (DR Payroll Control [default] or configured, CR Deduction Payable)
        $lines = $lines->merge($this->buildDeductionLines($cycle->Month, $cycle->Year, $settings, $postingMode));

        // Employer contributions (DR Employer Expense, CR Employer Liability)
        $lines = $lines->merge($this->buildEmployerContributionLines($run, $settings, $postingMode));

        // Gratuity accrual (DR Gratuity Expense, CR Gratuity Payable)
        $lines = $lines->merge($this->buildGratuityLines($run, $settings, $postingMode));

        $lines = $this->consolidateLines($lines);

        $totalDebit = round((float)$lines->sum('Debit'), 2);
        $totalCredit = round((float)$lines->sum('Credit'), 2);
        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw ValidationException::withMessages([
                'posting_mode' => "Posting out of balance. Debit {$totalDebit} != Credit {$totalCredit}.",
            ]);
        }

        $now = now();
        $journal = FinanceJournalEntry::create([
            'Date' => $journalDate,
            'IdempotencyKey' => $idempotencyKey,
            'Type' => 'normal',
            'ApprovalStatus' => 'draft',
            'Status' => 'draft',
            'Description' => "Payroll posting for {$cycle->Month}/{$cycle->Year} (Run #{$run->Id})",
            'SystemDescription' => 'HR Payroll Posting',
            'CurrencyID' => $settings->CurrencyID,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => $now,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => $now,
        ]);

        foreach ($lines as $line) {
            FinanceJournalLines::create([
                'JournalEntryId' => $journal->Id,
                'GLAccountID' => $line['GLAccountID'],
                'BranchID' => $line['BranchID'] ?? null,
                'DepartmentID' => $line['DepartmentID'] ?? null,
                'Debit' => $line['Debit'],
                'Credit' => $line['Credit'],
                'Amount' => $line['Amount'],
                'IsDebit' => (bool)$line['IsDebit'],
                'Narration' => $line['Narration'] ?? null,
                'SystemDescription' => $line['SystemDescription'] ?? null,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => $now,
            ]);
        }

        return $journal;
    }

    private function buildBaseSalaryLines(PayrollRun $run, PayrollGLSetting $settings, string $postingMode): Collection
    {
        $lines = collect();

        $run->loadMissing('lines.employee');
        if ($postingMode === 'summary') {
            $amount = (float)$run->lines->sum('BasicSalary');
            if ($amount <= 0) {
                return $lines;
            }
            $lines->push($this->debitLine($settings->BasicSalaryExpenseGLAccountID, $amount, null, null, "Base Salary {$run->cycle?->Month}/{$run->cycle?->Year}"));
            $lines->push($this->creditLine($settings->PayrollControlGLAccountID, $amount, null, null, 'Payroll Control (Base Salary)'));

            return $lines;
        }

        $grouped = $run->lines->groupBy(function ($line) {
            return ($line->employee?->BranchID ?? 0) . '|' . ($line->employee?->DepartmentID ?? 0);
        });

        foreach ($grouped as $key => $rows) {
            $amount = (float)$rows->sum('BasicSalary');
            if ($amount <= 0) {
                continue;
            }
            [$branchId, $deptId] = array_map('intval', explode('|', $key));
            $branchId = $branchId ?: null;
            $deptId = $deptId ?: null;

            $lines->push($this->debitLine($settings->BasicSalaryExpenseGLAccountID, $amount, $branchId, $deptId, 'Base Salary'));
            $lines->push($this->creditLine($settings->PayrollControlGLAccountID, $amount, $branchId, $deptId, 'Payroll Control (Base Salary)'));
        }

        return $lines;
    }

    private function buildAllowanceLines(int $month, int $year, PayrollGLSetting $settings, string $postingMode): Collection
    {
        $rows = MonthlyAllowance::where('Month', $month)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->whereNotNull('AllowanceID')
            ->get(['Id','EmployeeID','AllowanceID','Amount']);

        if ($rows->isEmpty()) {
            return collect();
        }

        $employeeIds = $rows->pluck('EmployeeID')->unique()->values();
        $employees = Employee::whereIn('Id', $employeeIds)->get(['Id','BranchID','DepartmentID'])->keyBy('Id');
        $allowanceIds = $rows->pluck('AllowanceID')->unique()->values();
        $allowances = PayrollAllowance::whereIn('Id', $allowanceIds)->get(['Id','Name','DebitGLAccountID','CreditGLAccountID'])->keyBy('Id');

        $missing = $allowances->filter(fn ($a) => empty($a->DebitGLAccountID))->map(fn ($a) => $a->Name)->values();
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'posting_mode' => 'Missing GL mapping (Debit GL) for allowances: ' . $missing->implode(', ') . '.',
            ]);
        }

        $lines = collect();
        foreach ($rows as $row) {
            $amount = (float)$row->Amount;
            if ($amount <= 0) {
                continue;
            }

            $emp = $employees->get($row->EmployeeID);
            $alw = $allowances->get($row->AllowanceID);
            if (! $alw) {
                continue;
            }

            $branchId = $postingMode === 'branch_department' ? ($emp?->BranchID ?? null) : null;
            $deptId = $postingMode === 'branch_department' ? ($emp?->DepartmentID ?? null) : null;
            $creditGl = $alw->CreditGLAccountID ?: $settings->PayrollControlGLAccountID;

            $lines->push($this->debitLine((int)$alw->DebitGLAccountID, $amount, $branchId, $deptId, $alw->Name));
            $lines->push($this->creditLine((int)$creditGl, $amount, $branchId, $deptId, 'Payroll Control (Allowance)'));
        }

        return $lines;
    }

    private function buildDeductionLines(int $month, int $year, PayrollGLSetting $settings, string $postingMode): Collection
    {
        $rows = MonthlyDeduction::where('Month', $month)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->whereNotNull('DeductionID')
            ->get(['Id','EmployeeID','DeductionID','Amount']);

        if ($rows->isEmpty()) {
            return collect();
        }

        $employeeIds = $rows->pluck('EmployeeID')->unique()->values();
        $employees = Employee::whereIn('Id', $employeeIds)->get(['Id','BranchID','DepartmentID'])->keyBy('Id');
        $deductionIds = $rows->pluck('DeductionID')->unique()->values();
        $deductions = PayrollDeduction::whereIn('Id', $deductionIds)->get(['Id','Name','DebitGLAccountID','CreditGLAccountID'])->keyBy('Id');

        $missing = $deductions->filter(fn ($d) => empty($d->CreditGLAccountID))->map(fn ($d) => $d->Name)->values();
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'posting_mode' => 'Missing GL mapping (Credit GL) for deductions: ' . $missing->implode(', ') . '.',
            ]);
        }

        $lines = collect();
        foreach ($rows as $row) {
            $amount = (float)$row->Amount;
            if ($amount <= 0) {
                continue;
            }

            $emp = $employees->get($row->EmployeeID);
            $ded = $deductions->get($row->DeductionID);
            if (! $ded) {
                continue;
            }

            $branchId = $postingMode === 'branch_department' ? ($emp?->BranchID ?? null) : null;
            $deptId = $postingMode === 'branch_department' ? ($emp?->DepartmentID ?? null) : null;
            $debitGl = $ded->DebitGLAccountID ?: $settings->PayrollControlGLAccountID;

            $lines->push($this->debitLine((int)$debitGl, $amount, $branchId, $deptId, 'Payroll Control (Deduction)'));
            $lines->push($this->creditLine((int)$ded->CreditGLAccountID, $amount, $branchId, $deptId, $ded->Name));
        }

        return $lines;
    }

    private function buildEmployerContributionLines(PayrollRun $run, PayrollGLSetting $settings, string $postingMode): Collection
    {
        $cycle = $run->cycle;
        if (! $cycle) {
            return collect();
        }

        $rows = PayrollEmployerContribution::where('PayrollRunID', $run->Id)
            ->get(['Id','EmployeeID','DeductionID','Amount']);

        if ($rows->isEmpty()) {
            return collect();
        }

        $employeeIds = $rows->pluck('EmployeeID')->unique()->values();
        $employees = Employee::whereIn('Id', $employeeIds)->get(['Id','BranchID','DepartmentID'])->keyBy('Id');
        $deductionIds = $rows->pluck('DeductionID')->unique()->values();
        $deductions = PayrollDeduction::whereIn('Id', $deductionIds)
            ->get(['Id','Name','EmployerDebitGLAccountID','EmployerCreditGLAccountID'])
            ->keyBy('Id');

        $missing = $deductions->filter(function ($d) {
            return empty($d->EmployerDebitGLAccountID) || empty($d->EmployerCreditGLAccountID);
        })->map(fn ($d) => $d->Name)->values();
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'posting_mode' => 'Missing Employer GL mapping for deductions: ' . $missing->implode(', ') . '.',
            ]);
        }

        $lines = collect();
        foreach ($rows as $row) {
            $amount = (float)$row->Amount;
            if ($amount <= 0) {
                continue;
            }

            $emp = $employees->get($row->EmployeeID);
            $ded = $deductions->get($row->DeductionID);
            if (! $ded) {
                continue;
            }

            $branchId = $postingMode === 'branch_department' ? ($emp?->BranchID ?? null) : null;
            $deptId = $postingMode === 'branch_department' ? ($emp?->DepartmentID ?? null) : null;

            $lines->push($this->debitLine((int)$ded->EmployerDebitGLAccountID, $amount, $branchId, $deptId, $ded->Name . ' (Employer)'));
            $lines->push($this->creditLine((int)$ded->EmployerCreditGLAccountID, $amount, $branchId, $deptId, $ded->Name . ' Payable (Employer)'));
        }

        return $lines;
    }

    private function buildGratuityLines(PayrollRun $run, PayrollGLSetting $settings, string $postingMode): Collection
    {
        $rows = GratuityAccrual::where('PayrollRunID', $run->Id)
            ->get(['EmployeeID','Amount']);
        if ($rows->isEmpty()) {
            return collect();
        }

        if (! $settings->GratuityExpenseGLAccountID || ! $settings->GratuityLiabilityGLAccountID) {
            throw ValidationException::withMessages([
                'posting_mode' => 'Configure Gratuity Expense/Liability GLs before posting gratuity accruals.',
            ]);
        }

        $employeeIds = $rows->pluck('EmployeeID')->unique()->values();
        $employees = Employee::whereIn('Id', $employeeIds)->get(['Id','BranchID','DepartmentID'])->keyBy('Id');

        $lines = collect();
        if ($postingMode === 'summary') {
            $amount = (float)$rows->sum('Amount');
            if ($amount <= 0) {
                return $lines;
            }
            $lines->push($this->debitLine((int)$settings->GratuityExpenseGLAccountID, $amount, null, null, 'Gratuity Accrual'));
            $lines->push($this->creditLine((int)$settings->GratuityLiabilityGLAccountID, $amount, null, null, 'Gratuity Payable'));

            return $lines;
        }

        $grouped = $rows->groupBy(function ($row) use ($employees) {
            $emp = $employees->get($row->EmployeeID);

            return ($emp?->BranchID ?? 0) . '|' . ($emp?->DepartmentID ?? 0);
        });

        foreach ($grouped as $key => $group) {
            $amount = (float)$group->sum('Amount');
            if ($amount <= 0) {
                continue;
            }
            [$branchId, $deptId] = array_map('intval', explode('|', $key));
            $branchId = $branchId ?: null;
            $deptId = $deptId ?: null;

            $lines->push($this->debitLine((int)$settings->GratuityExpenseGLAccountID, $amount, $branchId, $deptId, 'Gratuity Accrual'));
            $lines->push($this->creditLine((int)$settings->GratuityLiabilityGLAccountID, $amount, $branchId, $deptId, 'Gratuity Payable'));
        }

        return $lines;
    }

    private function consolidateLines(Collection $lines): Collection
    {
        return $lines
            ->groupBy(function ($l) {
                return implode('|', [
                    (int)$l['GLAccountID'],
                    (int)($l['BranchID'] ?? 0),
                    (int)($l['DepartmentID'] ?? 0),
                    $l['IsDebit'] ? 'D' : 'C',
                ]);
            })
            ->map(function ($group) {
                $first = $group->first();
                $debit = round((float)$group->sum('Debit'), 2);
                $credit = round((float)$group->sum('Credit'), 2);
                $amount = $first['IsDebit'] ? $debit : $credit;

                return array_merge($first, [
                    'Debit' => $debit,
                    'Credit' => $credit,
                    'Amount' => $amount,
                ]);
            })
            ->values();
    }

    private function debitLine(int $glAccountId, float $amount, ?int $branchId, ?int $deptId, string $narration): array
    {
        $amount = round($amount, 2);

        return [
            'GLAccountID' => $glAccountId,
            'BranchID' => $branchId,
            'DepartmentID' => $deptId,
            'Debit' => $amount,
            'Credit' => 0,
            'Amount' => $amount,
            'IsDebit' => true,
            'Narration' => $narration,
            'SystemDescription' => 'HR Payroll',
        ];
    }

    private function creditLine(int $glAccountId, float $amount, ?int $branchId, ?int $deptId, string $narration): array
    {
        $amount = round($amount, 2);

        return [
            'GLAccountID' => $glAccountId,
            'BranchID' => $branchId,
            'DepartmentID' => $deptId,
            'Debit' => 0,
            'Credit' => $amount,
            'Amount' => $amount,
            'IsDebit' => false,
            'Narration' => $narration,
            'SystemDescription' => 'HR Payroll',
        ];
    }
}
