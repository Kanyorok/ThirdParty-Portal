<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\AttendanceDaily;
use App\Models\HR\AttendanceLog;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function daily(Request $request)
    {
        $query = AttendanceDaily::with('employee');

        if ($request->filled('date_from')) {
            $query->where('WorkDate', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('WorkDate', '<=', $request->date_to);
        }
        if ($request->filled('employee_no')) {
            $employeeNo = $request->employee_no;
            $query->whereHas('employee', function ($q) use ($employeeNo) {
                $q->where('EmployeeNo', 'like', "%{$employeeNo}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $records = $query->orderByDesc('WorkDate')->paginate(50);

        return view('hr.attendance.daily', compact('records'));
    }

    public function logs(Request $request)
    {
        $query = AttendanceLog::with('employee');

        if ($request->filled('date_from')) {
            $query->where('LogTime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('LogTime', '<=', $request->date_to);
        }
        if ($request->filled('employee_no')) {
            $employeeNo = $request->employee_no;
            $query->whereHas('employee', function ($q) use ($employeeNo) {
                $q->where('EmployeeNo', 'like', "%{$employeeNo}%");
            });
        }
        if ($request->filled('channel')) {
            $query->where('Channel', $request->channel);
        }

        $logs = $query->orderByDesc('LogTime')->paginate(100);

        return view('hr.attendance.logs', compact('logs'));
    }
}
