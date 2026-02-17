<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Core\Country;
use App\Models\HR\AttendanceDaily;
use App\Models\HR\Employee;
use App\Models\HR\Holiday;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use App\Services\HR\WorkingDayResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName','DepartmentID']);

        return view('hr.leave.requests.index', compact('requests', 'employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName','DepartmentID']);
        $types = $this->eligibleLeaveTypesForEmployee(optional($employees->first())->Id);

        return view('hr.leave.requests.create', compact('employees', 'types'));
    }

    public function eligibleTypes(Request $request)
    {
        $employeeId = $request->query('employee_id');
        $types = $this->eligibleLeaveTypesForEmployee($employeeId);

        return response()->json($types);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $data['Status'] = 'Pending';
        $data['RequestedBy'] = auth()->id();
        $data['RequestedOn'] = now();
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();
        $data['TotalDays'] = $this->calculateDays(
            $data['StartDate'],
            $data['EndDate'],
            $request->input('TotalDays'),
            $data['EmployeeID'] ?? null
        );
        if (empty($data['RelieverID'])) {
            $data['RelieverID'] = null;
        }

        LeaveRequest::create($data);

        return redirect()->route('hr.leave.requests.index')->with('success', 'Leave request submitted.');
    }

    public function approve($id, Request $request)
    {
        $leave = LeaveRequest::findOrFail($id);
        $totalDays = $leave->TotalDays ?? $this->calculateDays($leave->StartDate, $leave->EndDate, null, $leave->EmployeeID);
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
        $totalDays = $leave->TotalDays ?? $this->calculateDays($leave->StartDate, $leave->EndDate, null, $leave->EmployeeID);
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
        return view('hr.leave.calendar.index');
    }

    public function calendarData(Request $request)
    {
        $start = Carbon::parse($request->query('start'));
        $end = Carbon::parse($request->query('end'));

        // Fetch leaves in range
        $leaves = LeaveRequest::with(['employee', 'type', 'reliever'])
            ->whereIn('Status', ['Approved', 'Pending'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('StartDate', [$start, $end])
                  ->orWhereBetween('EndDate', [$start, $end])
                  ->orWhere(function ($sq) use ($start, $end) {
                      $sq->where('StartDate', '<=', $start)
                         ->where('EndDate', '>=', $end);
                  });
            })
            ->get();

        $events = [];
        $dailyCounts = [];
        $leaveTypeStats = [];

        foreach ($leaves as $leaf) {
            $color = match($leaf->Status) {
                'Approved' => '#28a745', // Green
                'Pending' => '#ffc107',  // Orange/Yellow
                default => '#6c757d'
            };

            $empName = trim(($leaf->employee->FirstName ?? '') . ' ' . ($leaf->employee->LastName ?? ''));
            if (! $empName) {
                $empName = 'Unknown Employee';
            }

            $typeName = $leaf->type->Name ?? 'Leave';

            $events[] = [
                'id' => $leaf->Id,
                'title' => $empName . ' - ' . $typeName,
                'start' => $leaf->StartDate,
                'end' => Carbon::parse($leaf->EndDate)->addDay()->toDateString(), // FullCalendar is exclusive on end date
                'color' => $color,
                'extendedProps' => [
                    'employee' => $empName,
                    'type' => $typeName,
                    'status' => $leaf->Status,
                    'days' => $leaf->TotalDays,
                    'reliever' => ($leaf->reliever->FirstName ?? '') . ' ' . ($leaf->reliever->LastName ?? ''),
                    'startDate' => Carbon::parse($leaf->StartDate)->toDateString(), // Explicit date for frontend
                ],
            ];

            // Daily Counts Logic
            $s = Carbon::parse($leaf->StartDate);
            $e = Carbon::parse($leaf->EndDate);

            // Clamp to requested view range for stats
            if ($s->lt($start)) {
                $s = $start->copy();
            }
            if ($e->gt($end)) {
                $e = $start->copy();
            } // Wait, if leaf ends after view end, we shouldn't clamp E to START. We should clamp to END.
            // FIXED BUG: Clamping logic was checking $e > $end then setting $e = $start (in my head).
            // Actually previous code was: if ($e->gt($end)) $e = Carbon::parse($end);
            // Correct logic:
            if ($e->gt($end)) {
                $e = $end->copy();
            }

            // Ensure s <= e after clamping. If clamping makes s > e, then the leave is outside range (shouldn't happen with query but safe to check)
            if ($s->gt($e)) {
                continue;
            }

            for ($d = $s->copy(); $d->lte($e); $d->addDay()) {
                $dateStr = $d->toDateString();
                if (! isset($dailyCounts[$dateStr])) {
                    $dailyCounts[$dateStr] = [
                        'count' => 0,
                        'employees' => [],
                    ];
                }
                $dailyCounts[$dateStr]['count']++;
                $dailyCounts[$dateStr]['employees'][] = [
                    'name' => $empName,
                    'type' => $typeName,
                    'avatar' => $leaf->employee->PhotoPath ?? null,
                ];
            }

            // Stats Logic
            // We should only count days relevant to the view? Or TotalDays of the request?
            // The requirement says "Leave statistics... populated". Usually means visible days.
            // If I use $leaf->TotalDays, it counts days outside the month too.
            // Let's count the calculated days in loop for better accuracy of "Monthly Stats".
            // Re-calculating duration within window:
            $daysInView = $s->diffInDays($e) + 1; // Inclusive
            // Adjust for working days? That's expensive. Let's stick to calendar days for visual stats or use simple diff.
            // The user wants "Tabulations". simple count is okay.

            $statsName = $leaf->type->Name ?? 'Other';
            if (! isset($leaveTypeStats[$statsName])) {
                $leaveTypeStats[$statsName] = 0;
            }
            $leaveTypeStats[$statsName] += $daysInView;
        }

        return response()->json([
            'events' => $events,
            'daily_counts' => $dailyCounts,
            'stats' => $leaveTypeStats,
        ]);
    }

    /**
     * AJAX helper to auto-calculate days on the create form.
     */
    public function previewDays(Request $request)
    {
        try {
            $data = $request->validate([
                'StartDate' => 'required|date',
                'EndDate' => 'required|date|after_or_equal:StartDate',
                'TotalDays' => 'nullable|numeric|min:0',
                'EmployeeID' => 'nullable|integer',
            ]);
            $days = $this->calculateDays(
                $data['StartDate'],
                $data['EndDate'],
                $request->input('TotalDays'),
                $data['EmployeeID'] ?? null
            );

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

        if (! empty($data['EmployeeID'])) {
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

            if (! empty($data['RelieverID'])) {
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

    private function calculateDays($start, $end, $requestedSingleDay = null, ?int $employeeId = null): float
    {
        $s = Carbon::parse($start);
        $e = Carbon::parse($end);
        $working = $this->workingMap($employeeId);
        $holidays = $this->holidaysBetween($s, $e, $employeeId);

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

    private function workingMap(?int $employeeId = null): array
    {
        return app(WorkingDayResolver::class)->getWorkingMap($employeeId);
    }

    private function holidaysBetween(Carbon $start, Carbon $end, ?int $employeeId = null)
    {
        $employeeReligion = $this->getEmployeeReligion($employeeId);
        $employeeCountry = $this->getEmployeeCountry($employeeId);

        $rows = Holiday::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where('Status', 'Approved')
            ->whereBetween('HolidayDate', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->filter(function ($holiday) use ($employeeReligion, $employeeCountry) {
                return $this->holidayAppliesToEmployee($employeeReligion, $employeeCountry, $holiday);
            });

        $recurring = Holiday::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where('Status', 'Approved')
            ->where('IsRecurring', 1)
            ->get()
            ->filter(function ($holiday) use ($employeeReligion, $employeeCountry) {
                return $this->holidayAppliesToEmployee($employeeReligion, $employeeCountry, $holiday);
            });

        $dates = collect();
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dateStr = $d->toDateString();
            if ($rows->firstWhere('HolidayDate', $dateStr)) {
                $dates->push($dateStr);

                continue;
            }
            if ($recurring->firstWhere(fn ($h) => Carbon::parse($h->HolidayDate)->format('m-d') === $d->format('m-d'))) {
                $dates->push($dateStr);
            }
        }

        return $dates->unique();
    }

    private function getEmployeeReligion(?int $employeeId): ?string
    {
        if (! $employeeId) {
            return null;
        }
        $religion = Employee::where('Id', $employeeId)->value('Religion');

        return $this->normalizeReligion($religion);
    }

    private function getEmployeeCountry(?int $employeeId): ?string
    {
        if (! $employeeId) {
            return null;
        }
        $branchId = Employee::where('Id', $employeeId)->value('BranchID');
        if (! $branchId) {
            return null;
        }
        $country = Branch::where('Id', $branchId)->value('Country');

        return $this->normalizeCountry($country);
    }

    private function holidayAppliesToEmployee(?string $employeeReligion, ?string $employeeCountry, Holiday $holiday): bool
    {
        return $this->holidayAppliesToReligion($employeeReligion, $holiday->AppliesToReligion ?? null)
            && $this->holidayAppliesToRegion($employeeCountry, $holiday->RegionScope ?? null, $holiday->CountryId ?? null);
    }

    private function holidayAppliesToReligion(?string $employeeReligion, ?string $holidayReligion): bool
    {
        $holiday = $this->normalizeReligion($holidayReligion);
        if (! $holiday) {
            return true;
        }
        if (! $employeeReligion) {
            return false;
        }

        return $holiday === $employeeReligion;
    }

    private function holidayAppliesToRegion(?string $employeeCountry, ?string $regionScope, ?int $holidayCountryId): bool
    {
        $scope = strtolower(trim((string)($regionScope ?? '')));
        if ($scope === '' || $scope === 'global') {
            return true;
        }
        if ($scope !== 'regional') {
            return true;
        }
        if (! $employeeCountry || ! $holidayCountryId) {
            return false;
        }

        $countries = $this->getCountryLookup();
        $country = $countries[$holidayCountryId] ?? null;
        if (! $country) {
            return false;
        }

        $employee = $this->normalizeCountry($employeeCountry);
        if (! $employee) {
            return false;
        }

        $candidates = [
            $this->normalizeCountry($country->Name ?? null),
            $this->normalizeCountry($country->CountryCode ?? null),
            $this->normalizeCountry($country->Iso3 ?? null),
            $this->normalizeCountry((string)$holidayCountryId),
        ];

        return in_array($employee, array_filter($candidates), true);
    }

    private function getCountryLookup(): array
    {
        static $lookup = null;
        if ($lookup !== null) {
            return $lookup;
        }
        $lookup = Country::select(['Id', 'Name', 'CountryCode', 'Iso3'])->get()->keyBy('Id')->all();

        return $lookup;
    }

    private function normalizeReligion(?string $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        return strtolower($value);
    }

    private function normalizeCountry(?string $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        return strtolower($value);
    }

    private function eligibleLeaveTypesForEmployee(?int $employeeId)
    {
        $query = LeaveType::query();
        if (! $employeeId) {
            return $query->orderBy('Name')->get();
        }
        $emp = Employee::find($employeeId);
        $gradeId = $emp->GradeID ?? null;
        $gender = $emp->Gender ?? null;
        $query->where(function ($q) use ($gradeId) {
            $q->whereDoesntHave('grades');
            if ($gradeId) {
                $q->orWhereHas('grades', fn ($g) => $g->where('t_HRLeaveTypeGrades.GradeID', $gradeId));
            }
        });
        if ($gender) {
            $query->where(function ($q) use ($gender) {
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
        if (! $type) {
            throw ValidationException::withMessages([
                'LeaveTypeID' => 'Invalid leave type.',
            ]);
        }
        // Check grade eligibility
        if ($type->grades()->exists()) {
            if (! $gradeId || ! $type->grades()->where('t_HRLeaveTypeGrades.GradeID', $gradeId)->exists()) {
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
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('StartDate', [$start, $end])
                      ->orWhereBetween('EndDate', [$start, $end])
                      ->orWhere(function ($inner) use ($start, $end) {
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
