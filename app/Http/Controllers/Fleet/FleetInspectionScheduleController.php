<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\FleetInspectionSchedule;
use App\Services\FleetManagement\FleetInspectionScheduleService;
use App\Models\Core\CodeDetail;
use App\Models\Fleet\FleetVehicle;
use App\Models\HRM\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\FleetManagement\FleetInspectionScheduleRequest;

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
        $schedules = FleetInspectionSchedule::with(['inspector', 'inspectionStatus', 'vehicle'])
            ->where('CreatedBy', Auth::id())
            ->get();

        return view('fleet.compliance.inspection_schedule.index', compact('schedules'));
    }

    public function create()
    {
        $this->authorize('create', FleetInspectionSchedule::class);
        
        $branchId = Auth::user()->employee?->BranchId;

        $vehicles = FleetVehicle::where('IsActive', 1)->get();

        $inspectionStatus = CodeDetail::where('CodeID', 'InspectionStatus')
            ->orderBy('Value')
            ->get();

        $inspectors = Employee::where('BranchId', $branchId)
            ->select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');

        return view('fleet.compliance.inspection_schedule.create', compact('vehicles', 'inspectionStatus', 'inspectors'));
    }

    public function store(FleetInspectionScheduleRequest $request)
    {
        $this->authorize('create', FleetInspectionSchedule::class);
        $validated = $request->validated();

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

        $branchId = Auth::user()->employee?->BranchId;

        $vehicles = FleetVehicle::where('IsActive', 1)->get();

        $inspectionStatus = CodeDetail::where('CodeID', 'InspectionStatus')
            ->orderBy('Value')
            ->get();

        $inspectors = Employee::where('BranchId', $branchId)
            ->orderBy('FirstName')
            ->get(['Id', 'FirstName', 'LastName'])
            ->map(function ($inspector) {
                return [
                    'id' => $inspector->Id,
                    'name' => $inspector->FirstName . ' ' . $inspector->LastName,
                ];
            });

        return view('fleet.compliance.inspection_schedule.edit', compact('schedule', 'vehicles', 'inspectionStatus', 'inspectors'));
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
