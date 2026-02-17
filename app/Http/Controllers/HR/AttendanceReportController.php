<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\AttendanceDaily;
use App\Models\HR\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('from', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('to', Carbon::now()->endOfMonth()->toDateString());
        $employeeId = $request->input('employee_id');

        $query = AttendanceDaily::query()
            ->whereBetween('WorkDate', [$startDate, $endDate]);

        if ($employeeId) {
            $query->where('EmployeeID', $employeeId);
        }

        // 1. Summary Cards
        $totalScheduled = (clone $query)->count(); // Total scheduled days (records)
        $totalPresent = (clone $query)->whereIn('Status', ['Present', 'Late'])->count();
        $totalAbsent = (clone $query)->where('Status', 'Absent')->count();
        $totalLate = (clone $query)->where('LateMinutes', '>', 0)->count();
        
        $totalHoursWorked = (clone $query)->sum('TotalHours');
        $totalOvertimeHours = (clone $query)->sum('OvertimeHours');
        $totalLateMinutes = (clone $query)->sum('LateMinutes');

        // 2. Line Chart: Daily Trend (Present vs Late)
        $dailyTrend = (clone $query)
            ->select('WorkDate', 
                DB::raw("SUM(CASE WHEN Status IN ('Present', 'Late') THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN LateMinutes > 0 THEN 1 ELSE 0 END) as late_count"),
                DB::raw("SUM(OvertimeHours) as ot_hours")
            )
            ->groupBy('WorkDate')
            ->orderBy('WorkDate')
            ->get();

        // 3. Pie Chart: Status Distribution
        $statusDist = (clone $query)
            ->select('Status', DB::raw('count(*) as count'))
            ->groupBy('Status')
            ->pluck('count', 'Status')
            ->toArray();

        // 4. Bar Chart: Top 5 Late Employees (Minutes)
        $topLateEmployees = AttendanceDaily::query()
            ->whereBetween('WorkDate', [$startDate, $endDate])
            ->where('LateMinutes', '>', 0)
            ->select('EmployeeID', DB::raw('SUM(LateMinutes) as total_late_min'))
            ->with('employee:Id,FirstName,LastName')
            ->groupBy('EmployeeID')
            ->orderByDesc('total_late_min')
            ->limit(5)
            ->get();

        // 5. Bar Chart: Top 5 Overtime Employees (Hours)
        $topOvertimeEmployees = AttendanceDaily::query()
            ->whereBetween('WorkDate', [$startDate, $endDate])
            ->where('OvertimeHours', '>', 0)
            ->select('EmployeeID', DB::raw('SUM(OvertimeHours) as total_ot_hours'))
            ->with('employee:Id,FirstName,LastName')
            ->groupBy('EmployeeID')
            ->orderByDesc('total_ot_hours')
            ->limit(5)
            ->get();

        $employees = Employee::orderBy('FirstName')->get(['Id', 'FirstName', 'LastName']);

        return view('hr.attendance.reports.index', compact(
            'startDate', 'endDate', 'employees',
            'totalScheduled', 'totalPresent', 'totalAbsent', 'totalLate',
            'totalHoursWorked', 'totalOvertimeHours', 'totalLateMinutes',
            'dailyTrend', 'statusDist', 'topLateEmployees', 'topOvertimeEmployees'
        ));
    }
}
