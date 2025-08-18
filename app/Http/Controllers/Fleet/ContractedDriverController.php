<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\FleetManagement\ContractedDriversRequest;
use App\Services\FleetManagement\ContractedDriverService;
use App\Models\Fleet\FleetContractedDriverLicense;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriver;
use App\Models\Auth\User;
use App\Models\HRM\Employee;
use App\Models\Fleet\FleetContractedDriverAssignment;
use Illuminate\Support\Facades\DB;
use App\Models\Fleet\ContractedDriver;

class ContractedDriverController extends Controller
{
    protected ContractedDriverService $driverService;

    public function __construct(ContractedDriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    // Show all contracted drivers
    public function index()
    {
        $drivers = ContractedDriver::where('IsActive', 1)->get();
        return view('fleet.contracted_drivers.index', compact('drivers'));
    }

    // Show create form
    public function create()
    {
        return view('fleet.contracted_drivers.create');
    }

    // Store new contracted driver
    public function store(ContractedDriversRequest $request)
    {
        $validated = $request->validated();
        $validated['IsActive'] = 1;

        $this->driverService->create($validated);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver registered successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $driver = ContractedDriver::findOrFail($id);
        return view('fleet.contracted_drivers.edit', compact('driver'));
    }

    // Update existing record
    public function update(ContractedDriversRequest $request, $id)
    {
        $driver = ContractedDriver::findOrFail($id);
        $validated = $request->validated();
        $validated['Status'] = $request->input('Status'); // Ensure Status is passed if required

        $this->driverService->update($driver, $validated);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver updated successfully.');
    }

    // Deactivate contracted driver
    public function deactivate($id)
    {
        $driver = ContractedDriver::findOrFail($id);
        $driver->update([
            'IsActive' => 0,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver deactivated.');
    }

// Show driver details
public function show($Id)
{
    $driver = ContractedDriver::findOrFail($Id);

    // Licenses for this driver
    $licenses = FleetContractedDriverLicense::where('ContractedDriverID', $Id)->get();

    // Assignments with related vehicle
    $assignments = FleetContractedDriverAssignment::where('DriverID', $Id)
        ->with('vehicle')
        ->orderByDesc('AssignmentDate')
        ->get();

    // All active vehicles
    $vehicles = FleetVehicle::where('IsActive', 1)->get();

     $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
        ->pluck('name', 'Id');

    return view('fleet.contracted_drivers.show', compact(
        'driver',
        'licenses',
        'assignments',
        'vehicles',
        'assigners'  // <-- add this
    ));
}


}
