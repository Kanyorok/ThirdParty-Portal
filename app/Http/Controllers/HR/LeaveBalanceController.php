<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\LeaveAccrual;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    private const MONTHLY_ACCRUAL_TYPE_IDS = [1, 2];

    public function index(Request $request)
    {
        $filter = $request->input('leave_type_filter', 'annual');
        $search = $request->input('employee_search');

        $leaveTypes = LeaveType::where('IsActive', 1)->orderBy('Name')->get();
        
        $query = $this->filteredBalances($filter);

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('FirstName', 'like', "%{$search}%")
                  ->orWhere('LastName', 'like', "%{$search}%")
                  ->orWhere('EmployeeNo', 'like', "%{$search}%");
            });
        }

        $balances = $query->orderBy('EmployeeID')
            ->paginate(15)
            ->withQueryString();

        return view('hr.leave.balances.index', compact('balances', 'leaveTypes', 'filter', 'search'));
    }

    public function accrueMonthly()
    {
        $today = Carbon::today();
        $monthlyPeriod = $today->format('Y-m');
        $annualPeriod = $today->format('Y');
        $monthlyTypes = LeaveType::with('grades')
            ->where('IsActive', 1)
            ->whereIn('Id', self::MONTHLY_ACCRUAL_TYPE_IDS)
            ->get();
        $annualTypes = LeaveType::with('grades')
            ->where('IsActive', 1)
            ->whereNotIn('Id', self::MONTHLY_ACCRUAL_TYPE_IDS)
            ->get();
        $employees = Employee::where('IsActive', 1)->get(['Id', 'GradeID', 'Gender']);

        $existingMonthly = LeaveAccrual::where('Period', $monthlyPeriod)
            ->get()
            ->mapWithKeys(fn (LeaveAccrual $accrual) => ["{$accrual->EmployeeID}-{$accrual->LeaveTypeID}" => true])
            ->toArray();

        $processedMonthly = 0;
        $annualLoadResult = null;
        if ($today->month === 1 && $today->day === 1) {
            $annualLoadResult = $this->loadYearlyBalancesInternal($annualPeriod);
        }
        foreach ($employees as $emp) {
            $gradeId = $emp->GradeID;

            foreach ($monthlyTypes as $type) {
                if (!$this->eligibleForEmployee($type, $gradeId, $emp->Gender)) {
                    continue;
                }
                $key = "{$emp->Id}-{$type->Id}";
                if (isset($existingMonthly[$key])) {
                    continue;
                }
                $monthly = ($type->AnnualEntitlementDays ?? 0) / 12;
                if ($monthly <= 0) {
                    continue;
                }
                LeaveBalance::accrueMonthlyForEmployee($emp, $type, $monthlyPeriod, $monthly);
                $existingMonthly[$key] = true;
                $processedMonthly++;
            }

        }

        if ($processedMonthly === 0 && ($annualLoadResult['processed'] ?? 0) === 0) {
            return redirect()->route('hr.leave.balances.index')
                ->with('warning', "Accrual for {$monthlyPeriod} and year {$annualPeriod} are already complete.");
        }

        $messages = [];
        if ($processedMonthly > 0) {
            $messages[] = $today->day === 1
                ? 'Monthly accrual posted for accruing leave types.'
                : "Accrual completed for {$processedMonthly} outstanding monthly records in {$monthlyPeriod}.";
        }
        if (!empty($annualLoadResult['processed'])) {
            $messages[] = "Loaded {$annualLoadResult['processed']} annual leave balances for {$annualPeriod}.";
        }

        return redirect()->route('hr.leave.balances.index')->with('success', implode(' ', $messages));
    }

    public function loadYearlyBalances(Request $request)
    {
        $year = $request->input('year', Carbon::today()->format('Y'));
        $result = $this->loadYearlyBalancesInternal($year);
        if ($result['processed'] === 0) {
            return redirect()->route('hr.leave.balances.index')
                ->with('warning', "Yearly load for {$year} is already complete.");
        }
        return redirect()->route('hr.leave.balances.index')
            ->with('success', "Loaded {$result['processed']} annual leave balances for {$year}.");
    }

    private function loadYearlyBalancesInternal(string $year): array
    {
        $types = LeaveType::with('grades')
            ->where('IsActive', 1)
            ->whereNotIn('Id', self::MONTHLY_ACCRUAL_TYPE_IDS)
            ->get();
        $employees = Employee::where('IsActive', 1)->get(['Id', 'GradeID', 'Gender']);
        $existing = LeaveAccrual::where('Period', $year)
            ->get()
            ->mapWithKeys(fn (LeaveAccrual $accrual) => ["{$accrual->EmployeeID}-{$accrual->LeaveTypeID}" => true])
            ->toArray();

        $processed = 0;
        foreach ($employees as $emp) {
            $gradeId = $emp->GradeID;
            foreach ($types as $type) {
                if (!$this->eligibleForEmployee($type, $gradeId, $emp->Gender)) {
                    continue;
                }
                $key = "{$emp->Id}-{$type->Id}";
                if (isset($existing[$key])) {
                    continue;
                }
                $annual = $type->AnnualEntitlementDays ?? 0;
                if ($annual <= 0) {
                    continue;
                }
                LeaveBalance::accrueMonthlyForEmployee($emp, $type, $year, $annual);
                $existing[$key] = true;
                $processed++;
            }
        }
        return ['processed' => $processed];
    }

    public function export(Request $request)
    {
        $filter = $request->input('leave_type_filter', 'annual');
        $balances = $this->filteredBalances($filter)->orderBy('EmployeeID')->get();
        $filename = 'leave_balances_' . now()->format('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($balances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Leave Type', 'Entitlement', 'Accrued', 'Taken', 'Balance']);
            foreach ($balances as $balance) {
                $employee = $balance->employee;
                $type = $balance->type;
                fputcsv($handle, [
                    trim(($employee->FirstName ?? '') . ' ' . ($employee->LastName ?? '')),
                    $type->Name ?? '',
                    $balance->Entitlement,
                    $balance->Accrued,
                    $balance->Taken,
                    $balance->Balance,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function filteredBalances(string $filter)
    {
        $query = LeaveBalance::with(['type', 'employee']);
        if ($filter === 'annual') {
            $query->whereIn('LeaveTypeID', self::MONTHLY_ACCRUAL_TYPE_IDS);
        } elseif ($filter === 'all') {
            // no additional filter
        } elseif (str_starts_with($filter, 'type-')) {
            $typeId = (int) str_replace('type-', '', $filter);
            if ($typeId > 0) {
                $query->where('LeaveTypeID', $typeId);
            }
        }
        return $query;
    }

    private function eligibleForEmployee(LeaveType $type, ?int $gradeId, ?string $gender): bool
    {
        if (!empty($type->AllowedGender) && $gender) {
            if (strcasecmp($type->AllowedGender, $gender) !== 0) {
                return false;
            }
        }

        $grades = $type->grades;
        if ($grades->isEmpty()) {
            return true;
        }
        if (!$gradeId) {
            return false;
        }
        return $grades->contains('Id', $gradeId);
    }
}
