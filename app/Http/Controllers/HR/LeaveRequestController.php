<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use App\Models\HR\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\HR\LeaveBalance;
use App\Models\HR\AttendanceDaily;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee','type'])->orderByDesc('Id');
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('employee_id')) {
            $query->where('EmployeeID', $request->employee_id);
        }
        $requests = $query->paginate(50);
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);
        return view('hr.leave.requests.index', compact('requests','employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);
        $types = LeaveType::orderBy('Name')->get();
        return view('hr.leave.requests.create', compact('employees','types'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['Status'] = 'Pending';
        $data['RequestedBy'] = auth()->id();
        $data['RequestedOn'] = now();
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        $data['TotalDays'] = $this->calculateDays($data['StartDate'], $data['EndDate']);

        LeaveRequest::create($data);
        return redirect()->route('hr.leave.requests.index')->with('success', 'Leave request submitted.');
    }

    public function approve($id, Request $request)
    {
        $leave = LeaveRequest::findOrFail($id);
        $totalDays = $this->calculateDays($leave->StartDate, $leave->EndDate);
        $leave->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);

        $this->applyBalanceAdjustment($leave->EmployeeID, $leave->LeaveTypeID, $totalDays * -1);
        $this->markAttendanceOnLeave($leave->EmployeeID, $leave->StartDate, $leave->EndDate);

        return redirect()->route('hr.leave.requests.index')->with('success', 'Leave approved.');
    }

    public function reject($id, Request $request)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'Status' => 'Rejected',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ApprovalComment' => $request->input('ApprovalComment'),
        ]);
        return redirect()->route('hr.leave.requests.index')->with('success', 'Leave rejected.');
    }

    public function cancel($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $totalDays = $this->calculateDays($leave->StartDate, $leave->EndDate);
        $leave->update([
            'Status' => 'Cancelled',
            'CancelledBy' => auth()->id(),
            'CancelledOn' => now(),
        ]);
        if ($leave->Status === 'Approved') {
            $this->applyBalanceAdjustment($leave->EmployeeID, $leave->LeaveTypeID, $totalDays);
        }
        return redirect()->route('hr.leave.requests.index')->with('success', 'Leave cancelled.');
    }

    private function validateData(Request $request, bool $requireEmployee = false): array
    {
        $data = $request->validate([
            'EmployeeID' => [$requireEmployee ? 'required' : 'nullable', 'integer'],
            'LeaveTypeID' => 'required|integer',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
            'TotalDays' => 'required|numeric|min:0',
            'Reason' => 'nullable|string|max:500',
            'Status' => ['nullable', Rule::in(['Pending','Approved','Rejected','Cancelled'])],
        ]);

        if (!empty($data['EmployeeID'])) {
            $eligible = LeaveBalance::where('EmployeeID', $data['EmployeeID'])
                ->where('LeaveTypeID', $data['LeaveTypeID'])
                ->exists();
            if (! $eligible) {
                throw ValidationException::withMessages([
                    'LeaveTypeID' => 'Selected leave type is not assigned to this employee.',
                ]);
            }

            $type = LeaveType::find($data['LeaveTypeID']);
            if ($type && $type->AllowedGender) {
                $emp = Employee::find($data['EmployeeID']);
                if ($emp && $emp->Gender && strcasecmp($emp->Gender, $type->AllowedGender) !== 0) {
                    throw ValidationException::withMessages([
                        'LeaveTypeID' => 'This leave type is restricted to '.$type->AllowedGender.' employees.',
                    ]);
                }
            }
        }

        return $data;
    }

    private function calculateDays($start, $end): float
    {
        $s = Carbon::parse($start);
        $e = Carbon::parse($end);
        return $s->diffInDays($e) + 1;
    }

    private function ensureBalance(int $employeeId, int $typeId): LeaveBalance
    {
        $balance = LeaveBalance::firstOrNew([
            'EmployeeID' => $employeeId,
            'LeaveTypeID' => $typeId,
        ]);
        if (! $balance->exists) {
            $type = LeaveType::find($typeId);
            $balance->Entitlement = $type->AnnualEntitlementDays ?? 0;
            $balance->Accrued = $type->IsAccruing ? 0 : ($type->AnnualEntitlementDays ?? 0);
            $balance->Taken = 0;
            $balance->Balance = ($balance->Accrued ?? 0);
        }
        return $balance;
    }

    private function applyBalanceAdjustment(int $employeeId, int $typeId, float $delta): void
    {
        $balance = $this->ensureBalance($employeeId, $typeId);
        $balance->Taken = ($balance->Taken ?? 0) + ($delta < 0 ? abs($delta) : 0 * 1);
        $balance->Balance = ($balance->Balance ?? 0) + $delta;
        $balance->UpdatedBy = auth()->id();
        $balance->UpdatedOn = now();
        $balance->save();
    }

    private function markAttendanceOnLeave(int $employeeId, $start, $end): void
    {
        $s = Carbon::parse($start);
        $e = Carbon::parse($end);
        for ($date = $s->copy(); $date->lte($e); $date->addDay()) {
            AttendanceDaily::updateOrCreate(
                ['EmployeeID' => $employeeId, 'WorkDate' => $date->toDateString()],
                [
                    'Status' => 'On Leave',
                    'IsManualAdjusted' => 1,
                    'AdjustmentReason' => 'Leave approved',
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                ]
            );
        }
    }
}
