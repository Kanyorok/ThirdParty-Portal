<?php

namespace App\Services\HR;

use App\Models\HR\MonthlyDeduction;
use App\Models\HR\StaffLoan;
use App\Models\HR\StaffLoanSchedule;
use Carbon\Carbon;

class StaffLoanService
{
    public function ensureSchedule(StaffLoan $loan): void
    {
        $hasSchedule = StaffLoanSchedule::where('StaffLoanID', $loan->Id)->exists();
        if ($hasSchedule) {
            return;
        }

        $tenure = (int)($loan->TenureMonths ?? 0);
        $installment = (float)($loan->InstallmentAmount ?? 0);
        if ($tenure <= 0 || $installment <= 0 || !$loan->StartDate) {
            return;
        }

        $balance = (float)($loan->Balance ?? 0);
        if ($balance <= 0) {
            $balance = (float)($loan->Principal ?? 0);
        }
        if ($balance <= 0) {
            return;
        }

        $rate = max(0, ((float)($loan->InterestRate ?? 0) / 100 / 12));
        $start = Carbon::parse($loan->StartDate)->startOfMonth();

        for ($i = 1; $i <= $tenure; $i++) {
            if ($balance <= 0) {
                break;
            }
            $interest = round($balance * $rate, 2);
            $principal = $installment - $interest;
            if ($principal < 0) {
                $principal = 0;
                $interest = $installment;
            }
            if ($principal > $balance) {
                $principal = $balance;
                $interest = $installment - $principal;
            }

            $principal = round(max(0, $principal), 2);
            $interest = round(max(0, $interest), 2);
            $totalDue = round($principal + $interest, 2);

            StaffLoanSchedule::create([
                'StaffLoanID' => $loan->Id,
                'InstallmentNo' => $i,
                'DueDate' => $start->copy()->addMonths($i - 1)->endOfMonth()->toDateString(),
                'PrincipalComponent' => $principal,
                'InterestComponent' => $interest,
                'TotalDue' => $totalDue,
                'Status' => 'Pending',
            ]);

            $balance = round($balance - $principal, 2);
            if ($balance <= 0) {
                $balance = 0;
            }
        }
    }

    public function applyRepaymentsForMonth(int $month, int $year, int $actorId): void
    {
        $deductions = MonthlyDeduction::whereNotNull('StaffLoanID')
            ->where('Month', $month)
            ->where('Year', $year)
            ->where('Status', 'Approved')
            ->orderBy('StaffLoanID')
            ->get();

        if ($deductions->isEmpty()) {
            return;
        }

        $loanIds = $deductions->pluck('StaffLoanID')->unique()->values();
        $loans = StaffLoan::whereIn('Id', $loanIds)->get()->keyBy('Id');

        $schedules = StaffLoanSchedule::whereIn('StaffLoanID', $loanIds)
            ->whereMonth('DueDate', $month)
            ->whereYear('DueDate', $year)
            ->get()
            ->groupBy('StaffLoanID');

        foreach ($loanIds as $loanId) {
            $loan = $loans->get($loanId);
            if (!$loan) {
                continue;
            }

            if (!isset($schedules[$loanId])) {
                $this->ensureSchedule($loan);
                $schedules = StaffLoanSchedule::whereIn('StaffLoanID', $loanIds)
                    ->whereMonth('DueDate', $month)
                    ->whereYear('DueDate', $year)
                    ->get()
                    ->groupBy('StaffLoanID');
            }

            $schedule = $schedules->get($loanId, collect())->firstWhere('Status', 'Pending');
            if (!$schedule) {
                continue;
            }

            $principalPaid = (float)($schedule->PrincipalComponent ?? 0);
            if ($principalPaid < 0) {
                $principalPaid = 0;
            }

            $schedule->update([
                'Status' => 'Paid',
                'PaidBy' => $actorId,
                'PaidOn' => now(),
            ]);

            $balance = (float)($loan->Balance ?? 0);
            $balance = round($balance - $principalPaid, 2);
            if ($balance < 0) {
                $balance = 0;
            }

            $loan->Balance = $balance;
            if ($balance <= 0) {
                $loan->Status = 'Closed';
            }
            $loan->ModifiedBy = $actorId;
            $loan->ModifiedOn = now();
            $loan->save();
        }
    }
}
