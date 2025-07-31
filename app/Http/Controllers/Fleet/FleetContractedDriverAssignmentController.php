<?php


namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\FleetContractedDriver;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetContractedDriverAssignment;
use Illuminate\Support\Facades\Auth;

class FleetContractedDriverAssignmentController extends Controller
{
    public function create(Request $request)
    {
        $driverId = $request->get('driver_id');
        if (!$driverId) {
            abort(404, 'Contracted Driver ID is required.');
        }

        $driver = ContractedDriver::findOrFail($driverId);
        $vehicles = FleetVehicle::where('IsActive', 1)->get();

        return view('fleet.contracted_driver_assignments.create', compact('driver', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'DriverID' => 'required|exists:t_FleetContractedDrivers,ID',
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'AssignmentDate' => 'required|date',
            'UnassignmentDate' => 'nullable|date|after_or_equal:AssignmentDate',
            'Purpose' => 'nullable|string|max:255',
            'Notes' => 'nullable|string',
        ]);

        FleetContractedDriverAssignment::create([
            ...$validated,
            'AssignedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.contracted_drivers.show', $validated['DriverID'])
            ->with('success', 'Vehicle assigned to contracted driver successfully.');
    }

    public function index($driverId)
    {
        $driver = ContractedDriver::findOrFail($driverId);
        $assignments = FleetContractedDriverAssignment::where('DriverID', $driverId)
            ->with('vehicle')
            ->orderByDesc('AssignmentDate')
            ->get();

        return view('fleet.contracted_driver_assignments.index', compact('driver', 'assignments'));
    }
}
