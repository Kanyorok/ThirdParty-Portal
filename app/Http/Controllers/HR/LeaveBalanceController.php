<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use App\Models\HR\Employee;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function index()
    {
        $balances = LeaveBalance::with(['type','employee'])->orderBy('EmployeeID')->paginate(50);
        return view('hr.leave.balances.index', compact('balances'));
    }

    public function accrueMonthly()
    {
        $period = now()->format('Y-m');
        $types = LeaveType::where('IsAccruing', 1)->get();
        $employees = Employee::where('IsActive', 1)->get();
        foreach ($types as $type) {
            $monthly = ($type->AnnualEntitlementDays ?? 0) / 12;
            if ($monthly <= 0) {
                continue;
            }
            foreach ($employees as $emp) {
                LeaveBalance::accrueMonthlyForEmployee($emp, $type, $period, $monthly);
            }
        }
        return redirect()->route('hr.leave.balances.index')->with('success', 'Monthly accrual posted for accruing leave types.');
    }
}
