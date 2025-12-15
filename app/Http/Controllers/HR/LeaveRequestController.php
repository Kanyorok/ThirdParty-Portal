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
use App\Models\HR\WorkingDaySetting;
use App\Models\HR\Holiday;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee','type','reliever'])->orderByDesc('Id');
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
        $types = $this->eligibleLeaveTypesForEmployee(optional($employees->first())->Id);
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
        $data['TotalDays'] = $this->calculateDays($data['StartDate'], $data['EndDate'], $request->input('TotalDays'));
        if (empty($data['RelieverID'])) {
            $data['RelieverID'] = null;
        }

        LeaveRequest::create($data);
        return redirect()->route('hr.leave.requests.index')->with('success', 'Leave request submitted.');
    }

    public function approve($id, Request $request)
    {
        $leave = LeaveRequest::findOrFail($id);
        $totalDays = $leave->TotalDays ?? $this->calculateDays($leave->StartDate, $leave->EndDate);
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
        $totalDays = $leave->TotalDays ?? $this->calculateDays($leave->StartDate, $leave->EndDate);
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

    public function calendar()
    {
        $events = LeaveRequest::with('employee', 'type', 'reliever')
            ->whereIn('Status', ['Approved', 'Pending'])
            ->orderBy('StartDate')
            ->get();
        return view('hr.leave.calendar.index', compact('events'));
    }

    /**
     * AJAX helper to auto-calculate days on the create form.
     */
    public function previewDays(Request $request)
    {
        try {
            $data = $request->validate([
                'StartDate' => 'required|date',
                'EndDate'   => 'required|date|after_or_equal:StartDate',
                'TotalDays' => 'nullable|numeric|min:0',
            ]);
            $days = $this->calculateDays($data['StartDate'], $data['EndDate'], $request->input('TotalDays'));
            return response()->json(['days' => $days]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Unable to calculate days.'], 500);
        }
    }

    private function validateData(Request $request, bool $requireEmployee = false): array
    {
        $data = $request->validate([
            'EmployeeID' => [$requireEmployee ? 'required' : 'nullable', 'integer'],
            'LeaveTypeID' => 'required|integer',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date|after_or_equal:StartDate',
            'TotalDays' => 'required|numeric|min:0',
            'RelieverID' => 'nullable|integer',
            'Reason' => 'nullable|string|max:500',
            'Status' => ['nullable', Rule::in(['Pending','Approved','Rejected','Cancelled'])],
        ]);

        if (!empty($data['EmployeeID'])) {
            $this->assertEmployeeEligibleForLeaveType($data['EmployeeID'], $data['LeaveTypeID']);
            $type = LeaveType::find($data['LeaveTypeID']);
            if ($type && $type->AllowedGender) {
                $emp = Employee::find($data['EmployeeID']);
                if ($emp && $emp->Gender && strcasecmp($emp->Gender, $type->AllowedGender) !== 0) {
                    throw ValidationException::withMessages([
                        'LeaveTypeID' => 'This leave type is restricted to '.$type->AllowedGender.' employees.',
                    ]);
                }
            }

            if (!empty($data['RelieverID'])) {
                if ($data['RelieverID'] == $data['EmployeeID']) {
                    throw ValidationException::withMessages([
                        'RelieverID' => 'Reliever cannot be the same employee.',
                    ]);
                }
                $emp = $emp ?? Employee::find($data['EmployeeID']);
                $rel = Employee::find($data['RelieverID']);
                if ($emp && $rel && $emp->DepartmentID && $rel->DepartmentID && $emp->DepartmentID !== $rel->DepartmentID) {
                    throw ValidationException::withMessages([
                        'RelieverID' => 'Please choose a reliever from the same department.',
                    ]);
                }
            }
        }

        return $data;
    }

    private function calculateDays($start, $end, $requestedSingleDay = null): float
    {
        $s = Carbon::parse($start);
        $e = Carbon::parse($end);
        $working = $this->workingMap();
        $holidays = $this->holidaysBetween($s, $e);

        if ($s->isSameDay($e)) {
            $fraction = $working[$s->dayOfWeek]['fraction'] ?? 0;
            $allowed = $holidays->contains($s->toDateString()) ? 0 : $fraction;
            if ($requestedSingleDay) {
                if ($requestedSingleDay > $allowed) {
                    throw ValidationException::withMessages([
                        'TotalDays' => "Requested days exceed allowed for that day (max {$allowed}).",
                    ]);
                }
                if ($requestedSingleDay <= 0) {
                    throw ValidationException::withMessages([
                        'TotalDays' => 'Total days must be greater than zero.',
                    ]);
                }
                return round($requestedSingleDay, 2);
            }
            if ($allowed <= 0) {
                throw ValidationException::withMessages([
                    'TotalDays' => 'Selected day is non-working or holiday.',
                ]);
            }
            return round($allowed, 2);
        }

        $total = 0;
        for ($date = $s->copy(); $date->lte($e); $date->addDay()) {
            $w = $working[$date->dayOfWeek] ?? ['working' => false, 'fraction' => 0];
            $fraction = $w['fraction'] ?? 0;
            if ($fraction <= 0) {
                continue;
            }
            if ($holidays->contains($date->toDateString())) {
                continue;
            }
            $total += $fraction;
        }
        if ($total <= 0) {
            throw ValidationException::withMessages([
                'TotalDays' => 'Selected range has no working days.',
            ]);
        }
        return round($total, 2);
    }

    private function workingMap(): array
    {
        $map = [];
        $records = WorkingDaySetting::all()->keyBy('DayOfWeek');
        foreach (range(0, 6) as $dow) {
            $rec = $records[$dow] ?? null;
            $defaultWorking = ($dow >= 1 && $dow <= 5);
            $workingFlag = $rec ? (bool)$rec->IsWorking : $defaultWorking;
            $fraction = $rec
                ? (float)($rec->DayFraction ?? ($workingFlag ? 1.0 : 0.0))
                : ($workingFlag ? 1.0 : 0.0);
            // Guard against legacy bad data where half-days were saved as 0
            if ($workingFlag && $fraction <= 0) {
                $fraction = 0.5;
            }
            $map[$dow] = [
                'working' => $workingFlag,
                'fraction' => $fraction,
            ];
        }
        return $map;
    }

    private function holidaysBetween(Carbon $start, Carbon $end)
    {
        $rows = Holiday::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where('Status', 'Approved')
            ->whereBetween('HolidayDate', [$start->toDateString(), $end->toDateString()])
            ->get();

        $recurring = Holiday::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where('Status', 'Approved')
            ->where('IsRecurring', 1)
            ->get();

        $dates = collect();
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr = $d->toDateString();
            if ($rows->firstWhere('HolidayDate', $dateStr)) {
                $dates->push($dateStr);
                continue;
            }
            if ($recurring->firstWhere(fn($h) => Carbon::parse($h->HolidayDate)->format('m-d') === $d->format('m-d'))) {
                $dates->push($dateStr);
            }
        }
        return $dates->unique();
    }

    private function eligibleLeaveTypesForEmployee(?int $employeeId)
    {
        $query = LeaveType::query();
        if (!$employeeId) {
            return $query->orderBy('Name')->get();
        }
        $emp = Employee::find($employeeId);
        $gradeId = $emp->GradeID ?? null;
        $gender = $emp->Gender ?? null;
        $query->where(function($q) use ($gradeId) {
            $q->whereDoesntHave('grades');
            if ($gradeId) {
                $q->orWhereHas('grades', fn($g) => $g->where('t_HRLeaveTypeGrades.GradeID', $gradeId));
            }
        });
        if ($gender) {
            $query->where(function($q) use ($gender) {
                $q->whereNull('AllowedGender')
                  ->orWhere('AllowedGender', $gender);
            });
        }
        return $query->orderBy('Name')->get();
    }

    private function assertEmployeeEligibleForLeaveType(int $employeeId, int $leaveTypeId): void
    {
        $emp = Employee::find($employeeId);
        $gradeId = $emp->GradeID ?? null;
        $type = LeaveType::with('grades')->find($leaveTypeId);
        if (!$type) {
            throw ValidationException::withMessages([
                'LeaveTypeID' => 'Invalid leave type.',
            ]);
        }
        // Check grade eligibility
        if ($type->grades()->exists()) {
            if (!$gradeId || !$type->grades()->where('t_HRLeaveTypeGrades.GradeID', $gradeId)->exists()) {
                throw ValidationException::withMessages([
                    'LeaveTypeID' => 'This leave type is not eligible for the employee’s job grade.',
                ]);
            }
        }
        // Check gender eligibility
        if ($type->AllowedGender && $emp && $emp->Gender && strcasecmp($emp->Gender, $type->AllowedGender) !== 0) {
            throw ValidationException::withMessages([
                'LeaveTypeID' => 'This leave type is restricted to '.$type->AllowedGender.' employees.',
            ]);
        }

        // Date overlap check with Pending/Approved
        $start = request('StartDate');
        $end = request('EndDate');
        if ($start && $end) {
            $overlap = LeaveRequest::where('EmployeeID', $employeeId)
                ->whereIn('Status', ['Pending','Approved'])
                ->where(function($q) use ($start, $end) {
                    $q->whereBetween('StartDate', [$start, $end])
                      ->orWhereBetween('EndDate', [$start, $end])
                      ->orWhere(function($inner) use ($start, $end) {
                          $inner->where('StartDate', '<=', $start)->where('EndDate', '>=', $end);
                      });
                })
                ->exists();
            if ($overlap) {
                throw ValidationException::withMessages([
                    'StartDate' => 'Dates overlap with an existing pending/approved leave.',
                ]);
            }
        }
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
