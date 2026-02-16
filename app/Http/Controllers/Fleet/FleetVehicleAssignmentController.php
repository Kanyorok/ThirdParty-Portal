<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetVehicleAssignmentRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleAssignment;
use App\Models\Fleet\FleetVehicleInspection;
use App\Models\HRM\Employee;
use App\Services\FleetManagement\FleetVehicleAssignmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        // fetch approved parent trips for UI (so delete -> index also has approved trips)
        $approvedStatusId = CodeDetail::where('CodeID', 'TripStatus')
            ->where('Description', 'Approved')
            ->value('ID');
        $fleetTrips = FleetTripLog::whereNull('ParentTripID')
            ->when($approvedStatusId, fn ($q) => $q->where('Status', $approvedStatusId))
            ->orderByDesc('TripStartDate')
            ->get();

        return view('fleet.assignments.index', compact('assignments', 'assigners', 'fleetTrips'));
    }

    /** Show create form */
    public function create()
    {
        // Get current user and their employee record
        $currentUser = Auth::user();
        $currentEmployee = $currentUser->employee;

        if (! $currentEmployee) {
            return redirect()->back()
                ->with('error', 'You must have an employee record to assign vehicles.');
        }

        // Fetch the CodeDetail ID for TripStatus = Approved
        $statusId = CodeDetail::where('CodeID', 'TripStatus')
            ->where('Description', 'Approved')
            ->value('ID');

        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')
            ->pluck('Description', 'ID');

        $fleetVehicles = FleetVehicle::all();
        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');

        // Only filter by status when we have a valid status id
        $fleetTrips = FleetTripLog::whereNull('ParentTripID')
            ->when($statusId, fn ($q) => $q->where('Status', $statusId))
            ->orderByDesc('TripStartDate')
            ->get();

        $fleetInspections = FleetVehicleInspection::all();

        return view('fleet.assignments.create', compact(
            'fleetVehicles',
            'assigners',
            'fleetTrips',
            'fleetInspections',
            'vehicleTypes',
            'currentEmployee'
        ));
    }

    /** Store a new assignment */
    public function store(FleetVehicleAssignmentRequest $request)
    {
        try {
            // Get current user's employee ID
            $currentEmployeeId = Auth::user()->employee?->Id;

            if (! $currentEmployeeId) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'You must have an employee record to assign vehicles.');
            }

            $validated = $request->validated();

            // Override AssignedBy with current employee ID
            $validated['AssignedBy'] = $currentEmployeeId;

            $this->service->create($validated);

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

        // Get all vehicles initially (will be filtered by JS based on selected trip)
        $fleetVehicles = FleetVehicle::all();

        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');

        // fetch only approved parent trips for the edit form
        $statusId = CodeDetail::where('CodeID', 'TripStatus')
            ->where('Description', 'Approved')
            ->value('ID');

        $fleetTrips = FleetTripLog::whereNull('ParentTripID')
            ->when($statusId, fn ($q) => $q->where('Status', $statusId))
            ->orderByDesc('TripStartDate')
            ->get();

        $fleetInspections = FleetVehicleInspection::all();

        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')
            ->pluck('Description', 'ID');

        return view('fleet.assignments.edit', compact(
            'assignment',
            'fleetVehicles',
            'assigners',
            'fleetTrips',
            'fleetInspections',
            'vehicleTypes'
        ));
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
            'tripDate' => $trip->TripStartDate,

        ]);
    }

    /** Get latest inspection for vehicle */
    public function getVehicleInspection($Id)
    {
        $lastInspection = FleetVehicleInspection::where('VehicleID', $Id)
            ->orderByDesc('InspectionDate')
            ->first();

        return response()->json([
            'lastInspectionDate' => $lastInspection?->InspectionDate,
        ]);
    }

    public function getVehicleDriver($vehicleId)
    {
        $vehicle = \App\Models\Fleet\FleetVehicle::with('fuelType')->find($vehicleId);

        $driverAssignment = \App\Models\Fleet\FleetDriverAssignment::where('VehicleID', $vehicleId)
            ->whereNull('DeletedOn')
            ->latest('AssignmentDate')
            ->first();

        if (! $driverAssignment) {
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
