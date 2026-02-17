<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetInspectionScheduleRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetInspectionSchedule;
use App\Models\Fleet\FleetVehicle;
use App\Models\HR\Employee;
use App\Services\FleetManagement\FleetInspectionScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FleetInspectionScheduleController extends Controller
{
    protected FleetInspectionScheduleService $schedules;

    public function __construct(FleetInspectionScheduleService $schedules)
    {
        $this->schedules = $schedules;
    }

    public function index()
    {
        $this->authorize('viewAny', FleetInspectionSchedule::class);

        // Get current user's employee ID
        $currentEmployeeId = Auth::user()->employee?->Id;

        // Show all schedules where current user is the inspector
        $schedules = FleetInspectionSchedule::with(['inspector', 'inspectionStatus', 'vehicle'])
            ->when($currentEmployeeId, function ($query) use ($currentEmployeeId) {
                return $query->where('Inspector', $currentEmployeeId);
            })
            ->get();

        return view('fleet.compliance.inspection_schedule.index', compact('schedules'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', FleetInspectionSchedule::class);

        // Get current user and their employee record
        $currentUser = $request->user();
        $currentEmployee = $currentUser->employee;

        if (! $currentEmployee) {
            return redirect()->back()
                ->with('error', 'You must have an employee record to schedule inspections.');
        }

        $vehicles = FleetVehicle::all();

        $inspectionStatus = CodeDetail::where('CodeID', 'InspectionStatus')
            ->orderBy('Value')
            ->get();

        // Pass current employee as the default inspector
        return view('fleet.compliance.inspection_schedule.create', compact(
            'vehicles',
            'inspectionStatus',
            'currentEmployee'
        ));
    }

    public function store(FleetInspectionScheduleRequest $request)
    {
        $this->authorize('create', FleetInspectionSchedule::class);

        // Get current user's employee ID
        $currentEmployeeId = Auth::user()->employee?->Id;

        if (! $currentEmployeeId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You must have an employee record to schedule inspections.');
        }

        $validated = $request->validated();

        // Override Inspector with current employee ID
        $validated['Inspector'] = $currentEmployeeId;

        $this->schedules->create($validated);

        return redirect()->route('fleet.inspection_schedule.index')
            ->with('success', 'Inspection schedule created successfully.');
    }

    public function show($id)
    {
        $this->authorize('view', FleetInspectionSchedule::class);
        $schedule = FleetInspectionSchedule::with(['vehicle', 'inspectionStatus', 'inspector'])
            ->findOrFail($id);

        return view('fleet.compliance.inspection_schedule.show', compact('schedule'));
    }

    public function edit($id)
    {
        $this->authorize('viewAny', FleetInspectionSchedule::class);
        $schedule = FleetInspectionSchedule::findOrFail($id);

        // Get current user and their employee record
        $currentUser = Auth::user();
        $currentEmployee = $currentUser->employee;

        if (! $currentEmployee) {
            return redirect()->back()
                ->with('error', 'You must have an employee record to edit inspections.');
        }

        $vehicles = FleetVehicle::all();

        $inspectionStatus = CodeDetail::where('CodeID', 'InspectionStatus')
            ->orderBy('Value')
            ->get();

        // Pass current employee
        return view('fleet.compliance.inspection_schedule.edit', compact(
            'schedule',
            'vehicles',
            'inspectionStatus',
            'currentEmployee'
        ));
    }

    public function update(FleetInspectionScheduleRequest $request, $id)
    {
        $validated = $request->validated();

        $schedule = FleetInspectionSchedule::findOrFail($id);

        $this->schedules->update($schedule, $validated);

        return redirect()->route('fleet.inspection_schedule.index')
            ->with('success', 'Inspection record updated successfully.');
    }

    public function destroy($id)
    {
        $schedule = FleetInspectionSchedule::findOrFail($id);

        $this->schedules->delete($schedule);

        return redirect()->route('fleet.inspection_schedule.index')
            ->with('success', 'Inspection record deactivated.');
    }
}
