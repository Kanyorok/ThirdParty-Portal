<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\AttendanceLog;
use App\Models\HR\Employee;
use Illuminate\Http\Request;

class AttendanceRawLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceLog::with('employee')->orderByDesc('LogTime');
        if ($request->filled('from')) {
            $query->whereDate('LogTime', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('LogTime', '<=', $request->to);
        }
        if ($request->filled('employee_id')) {
            $query->where('EmployeeID', $request->employee_id);
        }
        if ($request->filled('channel')) {
            $query->where('Channel', $request->channel);
        }
        $logs = $query->paginate(50);
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);

        return view('hr.attendance.logs.index', compact('logs', 'employees'));
    }
}
