<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetDriverAssignmentRequest;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriverAssignment;
use App\Models\HRM\Employee;
use App\Services\FleetManagement\FleetDriverAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Core\CodeDetail;


class FleetDriverAssignmentController extends Controller
{
    protected FleetDriverAssignmentService $service;

    public function __construct(FleetDriverAssignmentService $service)
    {
        $this->service = $service;
    }

    // ================= CREATE =================
    public function create(Request $request)
    {
        $driverId = $request->get('DriverID');
        if (!$driverId) {
            abort(404, 'Driver ID is required.');
        }

        $driver = \App\Models\Fleet\FleetDriver::findOrFail($driverId); // <-- FIXED
        $activeStatusId = CodeDetail::where('CodeID', 'VehicleStatus')
            ->where('Description', 'Active')
            ->value('Id');

        $vehicles = FleetVehicle::where('Status', $activeStatusId)->get();
        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');

        return view('fleet.driver_assignments.create', compact('driver', 'vehicles', 'assigners'));
    }

    // ================= STORE =================
    public function store(FleetDriverAssignmentRequest $request)
    {
        $validated = $request->validated();
        if (!isset($validated['AssignedBy'])) {
            $validated['AssignedBy'] = Auth::id();
        }

        $assignment = $this->service->create($validated);

        return redirect()
            ->route('fleet.drivers.show', $validated['DriverID'])
            ->with('success', 'Vehicle assigned to driver successfully.');
    }

    // ================= INDEX =================
    public function index()
    {
        $assignments = FleetDriverAssignment::with(['vehicle', 'driver', 'assignedBy'])
            ->orderByDesc('AssignmentDate')
            ->get();

        return view('fleet.driver_assignments.index', compact('assignments'));
    }

    // ================= EDIT =================
    public function edit($id)
    {
        // Fetch the assignment with its driver and vehicle
        $assignment = FleetDriverAssignment::with(['vehicle', 'assignedBy', 'driver'])
            ->findOrFail($id);

        // Optionally, ensure only assignments of this driver can be fetched
        if (!$assignment->driver) {
            abort(404, 'Driver not found for this assignment.');
        }

        return response()->json($assignment);
    }

    // ================= UPDATE =================
    public function update(FleetDriverAssignmentRequest $request, $id)
    {
        $assignment = FleetDriverAssignment::findOrFail($id);
        $validated = $request->validated();

        if (!isset($validated['AssignedBy'])) {
            $validated['AssignedBy'] = $assignment->AssignedBy ?? Auth::id();
        }

        $this->service->update($assignment, $validated);
        $assignment->load(['vehicle', 'assignedBy']);

        return response()->json([
            'Id' => $assignment->Id,
            'vehicle' => $assignment->vehicle,
            'AssignmentDate' => $assignment->AssignmentDate,
            'UnassignmentDate' => $assignment->UnassignmentDate,
            'Purpose' => $assignment->Purpose,
            'assignedBy' => $assignment->assignedBy,
            'Notes' => $assignment->Notes,
        ]);
    }

    // ================= SHOW =================
    public function show($id)
    {
        $assignment = FleetDriverAssignment::with(['vehicle', 'driver', 'assignedBy'])
            ->findOrFail($id);

        return response()->json($assignment);
    }

    // ================= DESTROY =================
    public function destroy(Request $request, $id)
    {
        $assignment = FleetDriverAssignment::findOrFail($id);

        $this->service->delete($assignment);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Assignment deleted successfully.'
            ]);
        }

        return redirect()
            ->route('fleet.drivers.show', ['Id' => $assignment->DriverID])
            ->with('success', 'Assignment deleted successfully.');
    }

    // ================= DRIVER SHOW PAGE =================
    public function driverAssignments($driverId)
    {
        $driver = ContractedDriver::findOrFail($driverId);

        // Only fetch assignments belonging to this driver
        $assignments = $driver->assignments()->with(['vehicle', 'assignedBy'])->get();

        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');

        return view('fleet.drivers.show', compact('driver', 'assignments', 'vehicles', 'assigners'));
    }
}
