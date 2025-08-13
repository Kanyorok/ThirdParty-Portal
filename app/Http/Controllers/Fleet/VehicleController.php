<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\VehicleType;
use App\Models\Fleet\FuelType;
use App\Models\Core\CodeDetail;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\FleetModel;
use App\Models\Fleet\Branch;
use App\Models\Fleet\FleetVehicleAssignment;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    
public function index()
{
    $vehicles = FleetVehicle::with(['vehicleType', 'fuelType', 'branch'])
        ->where('CreatedBy', Auth::id())
        ->get();

    return view('fleet.vehicles.index', compact('vehicles'));
}

    public function create()
    {
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')
        ->orderBy('Value')
        ->get();
        $fuelTypes = FuelType::all();
        $branches = Branch::where('CreatedBy', 1)->get();
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();
        $vehicleStatuses = CodeDetail::where('CodeID', 'VehicleStatus')
        ->orderBy('Value')
        ->get();

        return view('fleet.vehicles.create', compact('vehicleTypes', 'fuelTypes', 'branches', 'brands', 'fleetModels', 'vehicleStatuses'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'RegistrationNumber' => 'required|string|max:50|unique:t_FleetVehicles,RegistrationNumber',
        'VehicleTypeID' => 'required|integer',
        'FuelTypeID' => 'required|integer',
        'Make' => 'nullable|string|max:100',
        'Model' => 'nullable|string|max:100',
        'YearOfManufacture' => 'nullable|integer',
        'ChassisNumber' => 'nullable|string|max:100',
        'EngineNumber' => 'nullable|string|max:100',
        'Capacity' => 'nullable|string|max:50',
        'OdometerReading' => 'nullable|numeric',
        'Status' => 'nullable|string|max:50',
        'AssignedBranchID' => 'nullable|integer',
        'AssignedToUserID' => 'nullable|integer',
    ]);

    $vehicle = FleetVehicle::create([
        ...$validated,
        'Status' => $validated['Status'] ?? 'Active',
        'CreatedBy' => Auth::id(),
        'CreatedOn' => now(),
    ]);

    // Optional: if either a branch or user was assigned, store it in assignment history
    if (!empty($validated['AssignedBranchID']) || !empty($validated['AssignedToUserID'])) {
        \App\Models\Fleet\FleetVehicleAssignment::create([
            'VehicleID' => $vehicle->VehicleID,
            'AssignedBranchID' => $validated['AssignedBranchID'],
            'AssignedToUserID' => $validated['AssignedToUserID'],
            'AssignmentDate' => now()->toDateString(),
            'Purpose' => 'Initial Assignment',
            'Notes' => 'Captured during vehicle registration',
            'AssignedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);
    }

    return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle registered successfully.');
}

    public function edit($id)
    {
        $vehicle = FleetVehicle::findOrFail($id);

        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')
        ->orderBy('Value')
        ->get();
        $fuelTypes = CodeDetail::where('CodeID', 'FuelType')
        ->orderBy('Value')
        ->get();
        $branches = Branch::where('CreatedBy', Auth::id())->get();

        return view('fleet.vehicles.edit', compact('vehicle', 'vehicleTypes', 'fuelTypes', 'branches'));
    }

    public function update(Request $request, $id)
{
    $vehicle = FleetVehicle::findOrFail($id);

    $validated = $request->validate([
        'RegistrationNumber' => 'required|string|max:50|unique:t_FleetVehicles,RegistrationNumber,' . $vehicle->VehicleID . ',VehicleID',
        'VehicleTypeID' => 'required|integer',
        'FuelTypeID' => 'required|integer',
        'Make' => 'nullable|string|max:100',
        'Model' => 'nullable|string|max:100',
        'YearOfManufacture' => 'nullable|integer',
        'ChassisNumber' => 'nullable|string|max:100',
        'EngineNumber' => 'nullable|string|max:100',
        'Capacity' => 'nullable|string|max:50',
        'OdometerReading' => 'nullable|numeric',
        'Status' => 'nullable|string|max:50',
        'AssignedBranchID' => 'nullable|integer',
        'AssignedToUserID' => 'nullable|integer',
    ]);

    // Check if reassignment occurred
    $isBranchChanged = $validated['AssignedBranchID'] != $vehicle->AssignedBranchID;
    $isUserChanged = $validated['AssignedToUserID'] != $vehicle->AssignedToUserID;

    // Update vehicle record
    $vehicle->update([
        ...$validated,
        'ModifiedBy' => Auth::id(),
        'ModifiedOn' => now(),
    ]);

    // Log assignment history if reassigned
    if ($isBranchChanged || $isUserChanged) {
        \App\Models\Fleet\FleetVehicleAssignment::create([
            'VehicleID' => $vehicle->VehicleID,
            'AssignedBranchID' => $validated['AssignedBranchID'],
            'AssignedToUserID' => $validated['AssignedToUserID'],
            'AssignmentDate' => now()->toDateString(),
            'Purpose' => 'Reassignment (via edit)',
            'Notes' => 'Updated from edit form',
            'AssignedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);
    }

    return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle updated successfully.');
}


    public function deactivate($id)
    {
        $vehicle = FleetVehicle::findOrFail($id);

        $vehicle->update([
            'IsActive' => 0,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle deregistered successfully.');
    }
}
