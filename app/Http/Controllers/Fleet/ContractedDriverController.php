<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\ContractedDriversRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetContractedDriverAssignment;
use App\Models\Fleet\FleetContractedDriverLicense;
use App\Models\Fleet\FleetVehicle;
use App\Models\HRM\Employee;
use App\Models\ThirdParty\SupplierMaster;
use App\Services\FleetManagement\ContractedDriverService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractedDriverController extends Controller
{
    protected ContractedDriverService $driverService;

    public function __construct(ContractedDriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    public function index()
    {
        $this->authorize('viewAny', ContractedDriver::class);

        $drivers = ContractedDriver::with(['company.party'])->latest('Id')->get();

        return view('fleet.contracted_drivers.index', compact('drivers'));
    }

    public function create()
    {
        $this->authorize('create', ContractedDriver::class);

        $companies = SupplierMaster::with('party')
            ->where('IsPrequalified', true)
            ->get();

        return view('fleet.contracted_drivers.create', compact('companies'));
    }

    public function store(ContractedDriversRequest $request)
    {
        $this->authorize('create', ContractedDriver::class);

        $validated = $request->validated();
        $document = $request->file('Document');

        $this->driverService->create($validated, $document);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver registered successfully.');
    }

    public function edit($id)
    {
        $this->authorize('update', ContractedDriver::class);

        $driver = ContractedDriver::with('company.party')->findOrFail($id);
        $companies = SupplierMaster::with('party')
            ->where('IsPrequalified', true)
            ->get();

        return view('fleet.contracted_drivers.edit', compact('driver', 'companies'));
    }

    public function update(ContractedDriversRequest $request, $id)
    {
        $this->authorize('update', ContractedDriver::class);

        $driver = ContractedDriver::findOrFail($id);

        $this->driverService->update($driver, $request->validated());

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver updated successfully.');
    }

    public function deactivate($id)
    {
        $driver = ContractedDriver::findOrFail($id);

        $driver->update([
            'IsActive' => false,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver deactivated.');
    }

    public function destroy($id)
    {
        $this->authorize('delete', ContractedDriver::class);

        $driver = ContractedDriver::findOrFail($id);
        $this->driverService->delete($driver);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver deleted successfully.');
    }

    public function show($id)
    {
        $this->authorize('view', ContractedDriver::class);

        $driver = ContractedDriver::with([
        'company.party',
        'assignments.vehicle.vehicleType',
        'tripLogs.vehicle',
        ])->findOrFail($id);

        $licenses = FleetContractedDriverLicense::where('ContractedDriverID', $id)
            ->orderByDesc('IssueDate')
            ->get();

        $assignments = $driver->assignments()
            ->with(['vehicle.vehicleType', 'assignedBy'])
            ->orderByDesc('AssignmentDate')
            ->get();

        $trips = $driver->tripLogs()
            ->with(['vehicle'])
            ->orderByDesc('TripStartDate')
            ->get();

        $activeVehicleStatus = CodeDetail::where('CodeID', 'VehicleStatus')
             ->where('Description', 'Active')
             ->value('ID');

        $assignedToContracted = FleetContractedDriverAssignment::whereNull('DeletedOn')
            ->pluck('VehicleID')
            ->toArray();

        $assignedToFleet = \App\Models\Fleet\FleetDriverAssignment::whereNull('DeletedOn')
            ->pluck('VehicleID')
            ->toArray();

        $assignedIds = array_unique(array_merge($assignedToContracted, $assignedToFleet));

        $vehicles = FleetVehicle::where('Status', $activeVehicleStatus)
            ->whereNotIn('Id', $assignedIds)
            ->with('vehicleType')
            ->get();

        $assigners = Employee::select(
            DB::raw("CONCAT(FirstName, ' ', LastName) as name"),
            'Id'
        )->pluck('name', 'Id');


        return view('fleet.contracted_drivers.show', compact(
            'driver',
            'licenses',
            'assignments',
            'trips',
            'vehicles',
            'assigners'
        ));
    }
}
