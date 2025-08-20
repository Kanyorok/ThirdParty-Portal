<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetRepairLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetRunningCost;
use App\Models\Fleet\FleetMaintenanceSchedule;

class FleetRepairLogController extends Controller
{
    public function index()
    {
        $repairs = FleetRepairLog::with('vehicle', 'schedule')->orderByDesc('RepairDate')->get();
        return view('fleet.maintenance.repair_logs.index', compact('repairs'));
    }

    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $schedules = FleetMaintenanceSchedule::where('Status', '!=', 'Cancelled')->orderByDesc('ScheduledDate')->get();
        return view('fleet.maintenance.repair_logs.create', compact('vehicles', 'schedules'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'VehicleID'         => 'required|exists:t_FleetVehicles,VehicleID',
        'RepairType'        => 'required|in:Normal,Emergency',
        'RepairDate'        => 'required|date',
        'Vendor'            => 'nullable|string|max:255',
        'Cost'              => 'nullable|numeric|min:0',
        'Description'       => 'nullable|string|max:1000',
        'Notes'             => 'nullable|string',
        'ScheduleID'        => 'nullable|exists:t_FleetMaintenanceSchedules,ID'
    ]);

    // Save the repair log first
    $repairLog = FleetRepairLog::create([
        ...$validated,
        'CreatedBy' => Auth::id(),
        'CreatedOn' => now(),
    ]);

    // Only save a running cost if cost is provided
    if (!empty($validated['Cost']) && $validated['Cost'] > 0) {
        FleetRunningCost::create([
            'VehicleID' => $validated['VehicleID'],
            'CostType' => 'Repair',
            'CostDate' => $validated['RepairDate'],
            'Amount' => $validated['Cost'],
            'ReferenceSource' => 'RepairLog',
            'ReferenceID' => $repairLog->id,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);
    }

    return redirect()->route('fleet.repair_logs.index')->with('success', 'Repair log recorded successfully.');
}

    public function show($id)
{
    $repair = FleetRepairLog::with(['vehicle', 'schedule'])->findOrFail($id);

    return view('fleet.maintenance.repair_logs.show', compact('repair'));
}
}
