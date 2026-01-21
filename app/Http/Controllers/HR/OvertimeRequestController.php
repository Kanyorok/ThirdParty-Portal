<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\OvertimeRequest;
use App\Models\HR\Employee;
use App\Models\HR\AttendanceDaily;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        $requests = OvertimeRequest::with('employee')->orderByDesc('WorkDate')->paginate(20);
        return view('hr.attendance.overtime.index', compact('requests'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);
        return view('hr.attendance.overtime.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => 'required|integer|exists:t_HREmployees,Id',
            'WorkDate' => 'required|date',
            'HoursRequested' => 'required|numeric|min:0',
            'Reason' => 'nullable|string|max:255',
        ]);
        $data['Status'] = 'Pending';
        $data['RequestedBy'] = auth()->id();
        $data['RequestedOn'] = now();
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        OvertimeRequest::create($data);
        return redirect()->route('hr.attendance.overtime.index')->with('success', 'Overtime request submitted.');
    }

    public function approve($id, Request $request)
    {
        $ot = OvertimeRequest::findOrFail($id);
        $ot->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);
        return redirect()->route('hr.attendance.overtime.index')->with('success', 'Overtime approved.');
    }

    public function reject($id, Request $request)
    {
        $ot = OvertimeRequest::findOrFail($id);
        $ot->update([
            'Status' => 'Rejected',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);
        return redirect()->route('hr.attendance.overtime.index')->with('success', 'Overtime rejected.');
    }

    public function syncFromAttendance(Request $request)
    {
        $data = $request->validate([
            'FromDate' => ['required', 'date'],
            'ToDate' => ['required', 'date', 'after_or_equal:FromDate'],
        ]);

        $start = Carbon::parse($data['FromDate'])->startOfDay();
        $end = Carbon::parse($data['ToDate'])->endOfDay();

        $attendanceRows = AttendanceDaily::whereBetween('WorkDate', [$start->toDateString(), $end->toDateString()])
            ->where('OvertimeHours', '>', 0)
            ->get(['EmployeeID', 'WorkDate', 'OvertimeHours']);

        if ($attendanceRows->isEmpty()) {
            return redirect()->route('hr.attendance.overtime.index')
                ->with('success', 'No overtime hours found in attendance logs.');
        }

        $existingKeys = OvertimeRequest::whereBetween('WorkDate', [$start->toDateString(), $end->toDateString()])
            ->get(['EmployeeID', 'WorkDate'])
            ->map(fn ($row) => $row->EmployeeID . '|' . $row->WorkDate->toDateString())
            ->flip();

        $now = now();
        $created = 0;
        foreach ($attendanceRows as $row) {
            $key = $row->EmployeeID . '|' . $row->WorkDate->toDateString();
            if ($existingKeys->has($key)) {
                continue;
            }

            OvertimeRequest::create([
                'EmployeeID' => $row->EmployeeID,
                'WorkDate' => $row->WorkDate->toDateString(),
                'HoursRequested' => (float)$row->OvertimeHours,
                'Status' => 'Pending',
                'Reason' => 'Auto from attendance logs',
                'RequestedBy' => auth()->id(),
                'RequestedOn' => $now,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
            ]);
            $created++;
        }

        return redirect()->route('hr.attendance.overtime.index')
            ->with('success', $created ? "Synced {$created} overtime requests." : 'No new overtime requests were created.');
    }
}
