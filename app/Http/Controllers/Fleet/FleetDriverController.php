<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetTripLog;
use Illuminate\Support\Facades\DB;
use App\Models\Fleet\FleetDriverAssignment;
use App\Models\Fleet\FleetDriverLicenseTracking;
use App\Services\FleetManagement\FleetDriverService;
use App\Models\Core\Approval\CodeDetail;
use App\Http\Requests\FleetManagement\FleetDriverRequest;
use App\Models\HR\Employee;
use App\Services\DMS\DocumentService;

class FleetDriverController extends Controller
{
    protected $fleetDriverService;

    public function __construct(FleetDriverService $fleetDriverService)
    {
        $this->fleetDriverService = $fleetDriverService;
    }

    public function index()
    {
       $this->authorize('viewAny', FleetDriver::class);
        $drivers = FleetDriver::with(['driver', 'employmentType'])
            ->where('CreatedBy', Auth::id())
            ->get();


        return view('fleet.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $this->authorize('create', FleetDriver::class);
        $branchId = Auth::user()->employee?->BranchId;

        $excludedIds = FleetDriver::pluck('StaffNumber')->toArray();

        $employeesQuery = Employee::where('BranchId', $branchId);

        if (!empty($excludedIds)) {
            $employeesQuery->whereNotIn('Id', $excludedIds);
        }

        $staffNo = $employeesQuery
            ->with('image')
            ->orderBy('LastName')
            ->get();

        $employmentType = CodeDetail::where('CodeID', 'EmploymentType')
            ->orderBy('Value')
            ->get();


        return view('fleet.drivers.create', compact('staffNo', 'employmentType'));
    }


        public function store(FleetDriverRequest $request)
        {
            $this->authorize('create', FleetDriver::class);

            $validated = $request->validated();
            $document = $request->file('Document');

            $validated = $request->validated();
            $imageId = $request->input('ImageId');

            $this->fleetDriverService->create(array_merge($validated, ['ImageId' => $imageId]), $document);
            return redirect()->route('fleet.drivers.index')
                ->with('success', 'Driver registered successfully.');
        }


    public function edit($id)
    {
        $this->authorize('edit', FleetDriver::class);
        $driver = FleetDriver::findOrFail($id);
        $branchId = Auth::user()->employee?->BranchId;

        $excludedIds = FleetDriver::where('Id', '!=', $driver->Id)->pluck('StaffNumber')->toArray();

        $employeesQuery = Employee::where('BranchId', $branchId);

        if (!empty($excludedIds)) {
            $employeesQuery->whereNotIn('Id', $excludedIds);
        }

        $staffNo = $employeesQuery
            ->with('image')
            ->orderBy('LastName')
            ->get();

        $employmentType = CodeDetail::where('CodeID', 'EmploymentType')
            ->orderBy('Value')
            ->get();

        return view('fleet.drivers.edit', compact('driver', 'staffNo', 'employmentType'));
    }

    public function update(FleetDriverRequest $request, $id)
    {
        $this->authorize('update', FleetDriver::class);

        $validated = $request->validated();
        $document = $request->file('Document');

        $this->fleetDriverService->update($id, $validated, $document);

        return redirect()->route('fleet.drivers.index')
            ->with('success', 'Driver details updated successfully.');
    }


    public function destroy($id)
    {
        $this->authorize('destroy', FleetDriver::class);
        $this->fleetDriverService->delete($id);
        return redirect()->route('fleet.drivers.index')
            ->with('success', 'Driver deactivated successfully.');
    }


  public function show($Id)
{
    $this->authorize('view', FleetDriver::class);

    $driver = FleetDriver::with([
        'assignments.vehicle',
        'trips'
    ])->findOrFail($Id);

    $licenses = FleetDriverLicenseTracking::where('DriverID', $Id)->get();

    $assignments = $driver->assignments()
        ->with('vehicle')
        ->orderByDesc('AssignmentDate')
        ->get();

    $activeStatusId = CodeDetail::where('CodeID', 'VehicleStatus')
        ->where('Description', 'Active')
        ->value('Id');

    // Vehicles already assigned to contracted drivers
    $assignedToContracted = \App\Models\Fleet\FleetContractedDriverAssignment::whereNull('DeletedOn')
        ->pluck('VehicleID')
        ->toArray();

    // Vehicles already assigned to fleet drivers
    $assignedToFleet = FleetDriverAssignment::whereNull('DeletedOn')
        ->pluck('VehicleID')
        ->toArray();

    $assignedIds = array_unique(array_merge($assignedToContracted, $assignedToFleet));

    // Only active + unassigned vehicles
    $vehicles = FleetVehicle::where('Status', $activeStatusId)
        ->whereNotIn('Id', $assignedIds)
        ->get();

    $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
        ->pluck('name', 'Id');

    return view('fleet.drivers.show', compact(
        'driver',
        'licenses',
        'assignments',
        'vehicles',
        'assigners'
    ));
}






}
