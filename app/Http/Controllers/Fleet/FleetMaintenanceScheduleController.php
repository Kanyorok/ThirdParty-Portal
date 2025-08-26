<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetMaintenanceScheduleRequest;
use App\Models\Fleet\FleetVehicle;
use App\Models\Core\CodeDetail;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetMaintenanceSchedule;
use App\Services\FleetManagement\FleetMaintenanceScheduleService;

class FleetMaintenanceScheduleController extends Controller
{
    protected FleetMaintenanceScheduleService $scheduleService;

    public function __construct(FleetMaintenanceScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    // View all maintenance schedules
    public function index()
    {
        $schedules = FleetMaintenanceSchedule::with('vehicle', 'maintenanceStatus')
            ->orderByDesc('ScheduleID', 'desc')
            ->get();

        return view('fleet.maintenance.schedule.index', compact('schedules'));
    }

    // Show form to create a new schedule
    public function create()
    {
        $vehicles = FleetVehicle::all();
        $maintenanceType = CodeDetail::where('CodeID', 'FleetMaintenanceType')
            ->orderBy('Value')
            ->get();

        return view('fleet.maintenance.schedule.create', compact('vehicles', 'maintenanceType'));
    }

    // Store a new maintenance schedule
    public function store(FleetMaintenanceScheduleRequest $request)
    {
        $data = $request->validated();
        $this->scheduleService->create($data);

        return redirect()
            ->route('fleet.maintenance_schedule.index')
            ->with('success', 'Maintenance schedule created successfully.');
    }

    // Show form to edit a schedule
    public function edit($id)
    {
        $schedule = FleetMaintenanceSchedule::findOrFail($id);
        $vehicles = FleetVehicle::all();
        $maintenanceType = CodeDetail::where('CodeID', 'FleetMaintenanceType')
            ->orderBy('Value')
            ->get();

        return view('fleet.maintenance.schedule.edit', compact('schedule', 'vehicles', 'maintenanceType'));
    }

    // Update a schedule (acknowledge)
    public function update(FleetMaintenanceScheduleRequest $request, $id)
    {
        $data = $request->validated();

        // Only handle mileage update & completion
        if (!empty($data['ScheduledMileage'])) {
            $this->scheduleService->updateMileage($id, $data['ScheduledMileage']);
        }

        return redirect()
            ->route('fleet.maintenance_schedule.index')
            ->with('success', 'Maintenance schedule updated successfully.');
    }


    // Complete a maintenance schedule
    public function complete($id)
    {
        $mileage = request()->input('ScheduledMileage');
        $this->scheduleService->complete($id, $mileage);

        return redirect()
            ->route('fleet.maintenance_schedule.index')
            ->with('success', 'Maintenance schedule marked as completed.');
    }

    // Show a single schedule
    public function show($id)
    {
        $schedule = FleetMaintenanceSchedule::with(['vehicle', 'maintenanceType', 'alert'])
            ->findOrFail($id);

        return view('fleet.maintenance.schedule.show', compact('schedule'));
    }

    // Cancel a schedule
    public function cancel($id)
    {
        $schedule = FleetMaintenanceSchedule::findOrFail($id);

        // Just deactivate, no change to MaintenanceStatus
        $schedule->Status = false;
        $schedule->ModifiedBy = Auth::id();
        $schedule->ModifiedOn = now();
        $schedule->save();

        activity()
            ->performedOn($schedule)
            ->causedBy(Auth::user())
            ->log('Maintenance Schedule Deactivated');

        return redirect()->back()->with('success', 'Schedule cancelled (deactivated) successfully.');
    }


    // Soft delete a schedule
    public function destroy($id)
    {
        $this->scheduleService->delete($id);

        return redirect()
            ->route('fleet.maintenance_schedule.index')
            ->with('success', 'Maintenance schedule deleted successfully.');
    }
}
