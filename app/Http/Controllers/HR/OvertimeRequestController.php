<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\OvertimeRequest;
use App\Models\HR\Employee;
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
            'EmployeeID' => 'required|integer',
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
}
