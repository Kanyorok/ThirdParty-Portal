<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetContractedDriverAssignmentRequest;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetContractedDriverAssignment;
use App\Models\Fleet\FleetVehicle;
use App\Models\HRM\Employee;
use App\Services\FleetManagement\FleetContractedDriverAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetContractedDriverAssignmentController extends Controller
{
    protected FleetContractedDriverAssignmentService $service;

    public function __construct(FleetContractedDriverAssignmentService $service)
    {
        $this->service = $service;
    }

    public function create(Request $request)
    {
        $driverId = $request->get('DriverID');

        if (! $driverId) {
            abort(404, 'Contracted Driver ID is required.');
        }

        $driver = ContractedDriver::findOrFail($driverId);

        // Get only vehicles that have no active assignment
        $vehicles = FleetVehicle::whereDoesntHave('assignments', function ($query) {
            $query->whereNull('DeletedOn');
        })->get();

        $assigners = Employee::select(
            DB::raw("CONCAT(LastName, ' ', FirstName) AS name"),
            'Id'
        )->pluck('name', 'Id');

        return view(
            'fleet.contracted_driver_assignments.create',
            compact('driver', 'vehicles', 'assigners')
        );
    }

    public function store(FleetContractedDriverAssignmentRequest $request)
    {
        $validated = $request->validated();
        if (! isset($validated['AssignedBy'])) {
            $validated['AssignedBy'] = Auth::id();
        }

        $assignment = $this->service->create($validated);

        return redirect()
            ->route('fleet.contracted_drivers.show', $validated['DriverID'])
            ->with('success', 'Vehicle assigned to contracted driver successfully.');
    }

    public function index()
    {
        $assignments = FleetContractedDriverAssignment::with(['vehicle', 'driver', 'assignedBy'])
            ->where('AssignedBy', Auth::id())
            ->orderByDesc('AssignmentDate')
            ->get();

        return view('fleet.contracted_driver_assignments.index', compact('assignments'));
    }

    public function edit($driverId, $assignmentId)
    {
        $assignment = FleetContractedDriverAssignment::with(['vehicle', 'assignedBy'])
            ->where('DriverID', $driverId)
            ->where('Id', $assignmentId)
            ->firstOrFail();

        return response()->json($assignment);
    }

    public function update(FleetContractedDriverAssignmentRequest $request, $driverId, $assignmentId)
    {
        $assignment = FleetContractedDriverAssignment::findOrFail($assignmentId);
        $validated = $request->validated();
        if (! isset($validated['AssignedBy'])) {
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

        return redirect()
            ->route('fleet.contracted_driver_assignments.index', $assignment->DriverID)
            ->with('success', 'Assignment updated successfully.');
    }

    public function show($id)
    {
        $driver = ContractedDriver::findOrFail($id);
        $licenses = $driver->licenses()->get();
        $assignments = $driver->assignments()->with('vehicle', 'assignedBy')->get();
        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');

        $vehicles = FleetVehicle::all();

        return view('fleet.contracted_drivers.show', compact('driver', 'licenses', 'assignments', 'assigners', 'vehicles'));
    }

    public function destroy(Request $request, $driverId, $assignmentId)
    {
        $assignment = FleetContractedDriverAssignment::findOrFail($assignmentId);

        $this->service->delete($assignment);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Assignment deleted successfully.',
            ]);
        }

        return redirect()
            ->route('fleet.contracted_drivers.show', ['Id' => $assignment->DriverID])
            ->with('success', 'Assignment deleted successfully.');
    }
}
