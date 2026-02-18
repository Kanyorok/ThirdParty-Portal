<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\AttendanceDaily;
use App\Models\HR\Employee;
use Illuminate\Http\Request;

class AttendanceDailySummaryController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceDaily::with('employee')->orderByDesc('WorkDate');
        if ($request->filled('from')) {
            $query->whereDate('WorkDate', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('WorkDate', '<=', $request->to);
        }
        if ($request->filled('employee_id')) {
            $query->where('EmployeeID', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        $dailies = $query->paginate(50);
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);

        return view('hr.attendance.daily.index', compact('dailies', 'employees'));
    }
}
