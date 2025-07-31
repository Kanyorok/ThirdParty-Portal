<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetDriverAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FleetDriverAssignmentController extends Controller
{
public function create(Request $request)
{
    $driverId = $request->get('driver_id');

    if (!$driverId) {
        abort(404, 'Driver ID is required');
    }

    $driver = FleetDriver::findOrFail($driverId);
    $vehicles = FleetVehicle::where('IsActive', 1)->get();

    return view('fleet.driver_assignments.create', compact('driver', 'vehicles'));
}
public function store(Request $request)
{
    $validated = $request->validate([
        'DriverID' => 'required|integer|exists:t_FleetDrivers,DriverID',
        'VehicleID' => 'required|integer|exists:t_FleetVehicles,VehicleID',
        'AssignmentDate' => 'required|date',
        'UnassignmentDate' => 'nullable|date|after_or_equal:AssignmentDate',
        'Purpose' => 'nullable|string|max:255',
        'Notes' => 'nullable|string',
    ]);

    FleetDriverAssignment::create([
        ...$validated,
        'AssignedBy' => Auth::id(),
        'CreatedOn' => now(),
    ]);

    return redirect()->route('fleet.drivers.index')->with('success', 'Driver assigned to vehicle successfully.');
}


public function index()
{
    $assignments = \App\Models\Fleet\FleetDriverAssignment::with(['vehicle', 'driver', 'assignedByUser'])
        ->orderByDesc('AssignmentDate')
        ->get();

    return view('fleet.driver_assignments.index', compact('assignments'));
}
}
