<?php

namespace App\Services\HR;

use App\Models\HR\Employee;
use App\Models\Auth\User;
use App\Models\HR\Exit\ExitPolicy;
use App\Models\HR\Exit\ExitPolicyNoticePeriod;
use App\Models\HR\Exit\ExitRequest;
use App\Models\HRM\Employee as LegacyEmployee;
use App\Models\HR\GratuityAccrual;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use Carbon\Carbon;

class ExitService
{
    public function resolveNoticePeriod(?ExitPolicy $policy, Employee $employee): array
    {
        if (!$policy) {
            return ['days' => 0, 'payInLieuAllowed' => true];
        }

        $periods = ExitPolicyNoticePeriod::where('PolicyID', $policy->Id)->get();
        $employmentType = $employee->EmploymentType;
        $contractType = $employee->ContractType;

        $match = $periods->first(function ($period) use ($employmentType, $contractType) {
            return $period->EmploymentType === $employmentType && $period->ContractType === $contractType;
        }) ?? $periods->first(function ($period) use ($employmentType) {
            return $period->EmploymentType === $employmentType && empty($period->ContractType);
        }) ?? $periods->first(function ($period) use ($contractType) {
            return empty($period->EmploymentType) && $period->ContractType === $contractType;
        }) ?? $periods->first(function ($period) {
            return empty($period->EmploymentType) && empty($period->ContractType);
        });

        if (!$match) {
            return ['days' => 0, 'payInLieuAllowed' => true];
        }

        return [
            'days' => (int)$match->NoticeDays,
            'payInLieuAllowed' => (bool)$match->PayInLieuAllowed,
        ];
    }

    public function calculateNoticePay(Employee $employee, int $noticeDays): float
    {
        $basic = (float)($employee->BasicSalary ?? 0);
        if ($basic <= 0 || $noticeDays <= 0) {
            return 0.0;
        }

        return round(($basic / 30) * $noticeDays, 2);
    }

    public function calculateSalaryToLastDay(Employee $employee, ?Carbon $effectiveDate): float
    {
        $basic = (float)($employee->BasicSalary ?? 0);
        if ($basic <= 0 || !$effectiveDate) {
            return 0.0;
        }

        $daysInMonth = max(1, $effectiveDate->daysInMonth);
        $workedDays = max(1, $effectiveDate->day);

        return round(($basic / $daysInMonth) * $workedDays, 2);
    }

    public function calculateLeaveEncashment(Employee $employee): float
    {
        $annualTypes = LeaveType::where('Code', 'like', 'ANNUAL%')->pluck('Id');
        if ($annualTypes->isEmpty()) {
            return 0.0;
        }

        $balance = LeaveBalance::where('EmployeeID', $employee->Id)
            ->whereIn('LeaveTypeID', $annualTypes)
            ->sum('Balance');

        $basic = (float)($employee->BasicSalary ?? 0);
        if ($basic <= 0 || $balance <= 0) {
            return 0.0;
        }

        return round(($basic / 30) * (float)$balance, 2);
    }

    public function calculateGratuity(Employee $employee): float
    {
        $amount = GratuityAccrual::where('EmployeeID', $employee->Id)->sum('Amount');
        return round((float)$amount, 2);
    }

    public function buildTerminalDues(ExitRequest $exit): array
    {
        $employee = $exit->employee;
        if (!$employee) {
            return [];
        }

        $effectiveDate = $exit->EffectiveExitDate
            ? Carbon::parse($exit->EffectiveExitDate)
            : ($exit->ProposedLastDay ? Carbon::parse($exit->ProposedLastDay) : null);

        $components = [];

        $salaryToDate = $this->calculateSalaryToLastDay($employee, $effectiveDate);
        if ($salaryToDate > 0) {
            $components[] = [
                'code' => 'SALARY_TO_DATE',
                'name' => 'Salary to last day',
                'amount' => $salaryToDate,
                'isEarning' => true,
                'isTaxable' => true,
            ];
        }

        $leaveEncashment = $this->calculateLeaveEncashment($employee);
        if ($leaveEncashment > 0) {
            $components[] = [
                'code' => 'LEAVE_ENCASH',
                'name' => 'Leave encashment',
                'amount' => $leaveEncashment,
                'isEarning' => true,
                'isTaxable' => true,
            ];
        }

        if ($exit->NoticePayInLieu) {
            $noticePay = (float)($exit->NoticePayAmount ?? 0);
            if ($noticePay <= 0) {
                $noticePay = $this->calculateNoticePay($employee, (int)($exit->NoticeDays ?? 0));
            }
            if ($noticePay > 0) {
                $components[] = [
                    'code' => 'NOTICE_PAY',
                    'name' => 'Notice pay',
                    'amount' => $noticePay,
                    'isEarning' => true,
                    'isTaxable' => true,
                ];
            }
        }

        $gratuity = $this->calculateGratuity($employee);
        if ($gratuity > 0) {
            $components[] = [
                'code' => 'GRATUITY',
                'name' => 'Gratuity accrual',
                'amount' => $gratuity,
                'isEarning' => true,
                'isTaxable' => false,
            ];
        }

        return $components;
    }

    public function deactivateLinkedUser(Employee $employee, int $actorId): void
    {
        $email = trim((string)($employee->Email ?? ''));
        $userIds = collect();

        if ($email !== '') {
            $userIds = User::where('Email', $email)->pluck('Id');
        }

        if ($userIds->isEmpty() && $employee->EmployeeNo) {
            $legacyEmployee = LegacyEmployee::where('EmployeeID', $employee->EmployeeNo)
                ->when($email !== '', function ($q) use ($email) {
                    $q->orWhere('Email', $email);
                })
                ->first();
            if ($legacyEmployee) {
                $userIds = User::where('EmployeeId', $legacyEmployee->Id)->pluck('Id');
            }
        }

        if ($userIds->isEmpty()) {
            return;
        }

        User::whereIn('Id', $userIds->all())->update([
            'IsActive' => 0,
            'ModifiedBy' => $actorId,
            'ModifiedOn' => now(),
        ]);
    }
}
