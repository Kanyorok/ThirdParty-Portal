<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\FleetManagement\ContractedDriversRequest;
use App\Services\FleetManagement\ContractedDriverService;
use App\Models\Fleet\FleetContractedDriverLicense;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetTripLog;
use App\Models\Core\CodeDetail;
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


    public function index()
    {
        $drivers = ContractedDriver::all();
        return view('fleet.contracted_drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('fleet.contracted_drivers.create');
    }

    public function store(ContractedDriversRequest $request)
    {
        $validated = $request->validated();
        $this->driverService->create($validated);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver registered successfully.');
    }

    public function edit($id)
    {
        $driver = ContractedDriver::findOrFail($id);
        return view('fleet.contracted_drivers.edit', compact('driver'));
    }


    public function update(ContractedDriversRequest $request, $id)
    {
        $driver = ContractedDriver::findOrFail($id);
        $validated = $request->validated();

        $this->driverService->update($driver, $validated);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver updated successfully.');
    }


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

    public function destroy($id)
    {
        $driver = ContractedDriver::findOrFail($id);
        $this->driverService->delete($driver);

        return redirect()->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver deleted successfully.');
    }


    public function show($Id)
    {
        $driver = ContractedDriver::findOrFail($Id);
        $licenses = FleetContractedDriverLicense::where('ContractedDriverID', $Id)->get();
        $assignments = FleetContractedDriverAssignment::where('DriverID', $Id)
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


        return view('fleet.contracted_drivers.show', compact(
            'driver',
            'licenses',
            'assignments',
            'vehicles',
            'assigners',
            'trips'
        ));
    }


}
