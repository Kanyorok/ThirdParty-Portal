<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetVehicleAssignmentRequest;
use App\Services\FleetManagement\FleetVehicleAssignmentService;
use App\Models\Fleet\FleetVehicleAssignment;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicleInspection;
use App\Models\HRM\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\User;

class FleetVehicleAssignmentController extends Controller
{
    protected FleetVehicleAssignmentService $service;

    public function __construct(FleetVehicleAssignmentService $service)
    {
        $this->service = $service;
        
    }

    /** Show all assignments */
    public function index()
    {
        $assignments = FleetVehicleAssignment::with(['vehicle', 'fleetVehicleType', 'driver', 'trip', 'assigner'])
            ->orderByDesc('CreatedOn')
            ->get();

        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');    

        return view('fleet.assignments.index', compact('assignments','assigners'));
    }

    /** Show create form */
    public function create()
    {
        $fleetVehicles = FleetVehicle::all();
        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');
        $fleetTrips = FleetTripLog::where('ParentTripID', null)->get();
        $fleetInspections = FleetVehicleInspection::all();

        return view('fleet.assignments.create', compact('fleetVehicles', 'assigners', 'fleetTrips', 'fleetInspections'));
    }

    /** Store a new assignment */
    public function store(FleetVehicleAssignmentRequest $request)
    {
        try {
            $this->service->create($request->validated());

            return redirect()->route('fleet.assignments.index')
                ->with('success', 'Vehicle assignment created successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['VehicleID' => $e->getMessage()])->withInput();
        }
    }

    /** Show a single assignment */
    public function show($id)
    {
        $assignment = FleetVehicleAssignment::with(['vehicle', 'fleetVehicleType', 'driver', 'trip', 'assigner'])
            ->where('Id', $id)
            ->firstOrFail();

        return view('fleet.assignments.show', compact('assignment'));
    }

    /** Show edit form */
   public function edit($id)
    {
        $assignment = FleetVehicleAssignment::with(['vehicle', 'fleetVehicleType', 'driver', 'trip', 'assigner'])
            ->where('Id', $id)
            ->firstOrFail();

        $fleetVehicles = FleetVehicle::all();
        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');
        $fleetTrips = FleetTripLog::where('ParentTripID', null)->get();
        $fleetInspections = FleetVehicleInspection::all();

        return view('fleet.assignments.edit', compact('assignment', 'fleetVehicles', 'assigners', 'fleetTrips', 'fleetInspections'));
    }


    /** Update an assignment */
    public function update(FleetVehicleAssignmentRequest $request, $id)
{
    try {
        $assignment = FleetVehicleAssignment::findOrFail($id);

        $this->service->update($assignment, $request->validated());

        return redirect()->route('fleet.assignments.index')
            ->with('success', 'Vehicle assignment updated successfully.');
    } catch (\Exception $e) {
        return back()->withErrors(['VehicleID' => $e->getMessage()])->withInput();
    }
}


    /** Delete an assignment */
    public function destroy($id)
    {
        $assignments = FleetVehicleAssignment::with(['vehicle', 'fleetVehicleType', 'driver', 'trip', 'assigner'])
            ->findOrFail($id);

        $this->service->delete($assignments);

        return redirect()->route('fleet.assignments.index')->with('success', 'Vehicle assignment deleted successfully.');
    }


    
    /* ------------------ AJAX HELPERS ------------------ */

    public function getVehiclesByTrip($Id)
    {
        $trip = FleetTripLog::findOrFail($Id);
        $vehicles = FleetVehicle::where('VehicleType', $trip->VehicleType)->get();

        return response()->json([
            'fleetVehicleType' => $trip->VehicleType,
            'vehicles' => $vehicles,
            'tripDate' => $trip->TripStartDate

        ]);
    }

    /** Get latest inspection for vehicle */
    public function getVehicleInspection($Id)
    {
        $lastInspection = FleetVehicleInspection::where('VehicleID', $Id)
            ->orderByDesc('InspectionDate')
            ->first();

        return response()->json([
            'lastInspectionDate' => $lastInspection?->InspectionDate
        ]);
    }

    public function getVehicleDriver($vehicleId)
    {
        $vehicle = \App\Models\Fleet\FleetVehicle::with('fuelType')->find($vehicleId);

        $driverAssignment = \App\Models\Fleet\FleetDriverAssignment::where('VehicleID', $vehicleId)
            ->whereNull('DeletedOn')
            ->latest('AssignmentDate')
            ->first();

        if (!$driverAssignment) {
            $driverAssignment = \App\Models\Fleet\FleetContractedDriverAssignment::where('VehicleID', $vehicleId)
                ->whereNull('DeletedOn')
                ->latest('AssignmentDate')
                ->first();
        }

        return response()->json([
            'driverId' => $driverAssignment?->DriverID,
            'driverName' => $driverAssignment?->driver?->FullName ?? 'No driver assigned',
            'fuelTypeId' => $vehicle?->fuelType?->Id,
            'fuelTypeName' => $vehicle?->fuelType?->FuelName,
        ]);
    }


}
