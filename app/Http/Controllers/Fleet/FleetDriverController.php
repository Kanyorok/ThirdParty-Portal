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
use App\Models\Core\CodeDetail;
use App\Http\Requests\FleetManagement\FleetDriverRequest;
use App\Models\HRM\Employee;
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
            ->orderBy('LastName')
            ->get(['Id', 'FirstName', 'LastName', 'EmployeeID', 'Email', 'Phone']);

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

            $this->fleetDriverService->create($validated, $document);

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
            ->orderBy('LastName')
            ->get(['Id', 'FirstName', 'LastName', 'EmployeeID', 'Email', 'Phone']);

        $employmentType = CodeDetail::where('CodeID', 'EmploymentType')
            ->orderBy('Value')
            ->get();

        return view('fleet.drivers.edit', compact('driver', 'staffNo', 'employmentType'));
    }

    public function update(FleetDriverRequest $request, $id)
    {
        $this->authorize('update', FleetDriver::class);
        $this->fleetDriverService->update($id, $request->validated());
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
    $driver = FleetDriver::findOrFail($Id);
    $licenses = FleetDriverLicenseTracking::where('DriverID', $Id)->get();
    $assignments = FleetDriverAssignment::where('DriverID', $Id)
        ->with('vehicle')
        ->orderByDesc('AssignmentDate')
        ->get();
    $vehicles = FleetVehicle::where('IsActive', 1)->get();

    $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
        ->pluck('name', 'Id');

  
      $trips = FleetTripLog::with(['vehicle'])
        ->where('DriverID', $driver->Id)
        ->orderByDesc('TripStartDate')
        ->get();

    return view('fleet.drivers.show', compact(
        'driver',
        'licenses',
        'assignments',
        'vehicles',
        'assigners',
        'trips'
    ));
}
    



}
