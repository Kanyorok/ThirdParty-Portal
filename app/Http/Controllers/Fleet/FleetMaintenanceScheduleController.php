<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetMaintenanceSchedule;

class FleetMaintenanceScheduleController extends Controller
{
    // View all maintenance schedules
    public function index()
    {
        $schedules = FleetMaintenanceSchedule::with('vehicle')->orderByDesc('ScheduledDate')->get();
        return view('fleet.maintenance.schedule.index', compact('schedules'));
    }

    // Show form to create a new schedule
    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->orderBy('RegistrationNumber')->get();
        return view('fleet.maintenance.schedule.create', compact('vehicles'));
    }

    // Store a new maintenance schedule
    public function store(Request $request)
    {
        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'MaintenanceType' => 'required|string|max:100',
            'ScheduledDate' => 'required|date',
            'ScheduledMileage' => 'nullable|integer|min:0',
            'Location' => 'nullable|string|max:255',
            'Notes' => 'nullable|string'
        ]);

        FleetMaintenanceSchedule::create([
            ...$validated,
            'Status' => 'Scheduled',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now()
        ]);

        return redirect()->route('fleet.maintenance_schedule.index')->with('success', 'Maintenance schedule created.');
    }

    // Show form to edit a schedule
    public function edit($id)
    {
        $schedule = FleetMaintenanceSchedule::findOrFail($id);
        $vehicles = FleetVehicle::where('IsActive', 1)->orderBy('RegistrationNumber')->get();

        return view('fleet.maintenance.schedule.edit', compact('schedule', 'vehicles'));
    }

    // Update a schedule
    public function update(Request $request, $id)
    {
        $schedule = FleetMaintenanceSchedule::findOrFail($id);

        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'MaintenanceType' => 'required|string|max:100',
            'ScheduledDate' => 'required|date',
            'ScheduledMileage' => 'nullable|integer|min:0',
            'Location' => 'nullable|string|max:255',
            'Notes' => 'nullable|string',
            'Status' => 'required|in:Scheduled,Completed,Cancelled'
        ]);

        $schedule->update([
            ...$validated,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now()
        ]);

        return redirect()->route('fleet.maintenance_schedule.index')->with('success', 'Maintenance schedule updated.');
    }

    // Optionally cancel a schedule
    public function cancel($id)
    {
        $schedule = FleetMaintenanceSchedule::findOrFail($id);
        $schedule->Status = 'Cancelled';
        $schedule->ModifiedBy = Auth::id();
        $schedule->ModifiedOn = now();
        $schedule->save();

        return redirect()->back()->with('success', 'Schedule cancelled.');
    }
}
