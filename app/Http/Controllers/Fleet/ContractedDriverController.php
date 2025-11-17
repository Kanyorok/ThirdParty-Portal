<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\FleetManagement\ContractedDriversRequest;
use App\Services\FleetManagement\ContractedDriverService;
use App\Models\Fleet\FleetContractedDriverLicense;
use App\Models\Fleet\FleetContractedDriverAssignment;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetTripLog;
use App\Models\HRM\Employee;
use App\Models\ThirdParty\ThirdParties;
use App\Models\Fleet\ContractedDriver;

class ContractedDriverController extends Controller
{
    protected ContractedDriverService $driverService;

    public function __construct(ContractedDriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    /**
     * Display a listing of the contracted drivers.
     */
    public function index()
    {
        $this->authorize('viewAny', ContractedDriver::class);

        $drivers = ContractedDriver::with('company')->get();

        return view('fleet.contracted_drivers.index', compact('drivers'));
    }

    /**
     * Show the form for creating a new contracted driver.
     */
    public function create()
    {
        $this->authorize('create', ContractedDriver::class);

        $companies = ThirdParties::where('IsPrequalified', true)->get();

        return view('fleet.contracted_drivers.create', compact('companies'));
    }

    /**
     * Store a newly created contracted driver in storage.
     */
    public function store(ContractedDriversRequest $request)
    {
        $this->authorize('create', ContractedDriver::class);

        $validated = $request->validated();
        $document = $request->file('Document');

        $this->driverService->create($validated, $document);

        return redirect()
            ->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver registered successfully.');
    }

    /**
     * Show the form for editing the specified contracted driver.
     */
    public function edit($id)
    {
        $this->authorize('update', ContractedDriver::class);

        $driver = ContractedDriver::findOrFail($id);
        $companies = ThirdParties::where('IsPrequalified', true)->get();

        return view('fleet.contracted_drivers.edit', compact('driver', 'companies'));
    }

    /**
     * Update the specified contracted driver in storage.
     */
    public function update(ContractedDriversRequest $request, $id)
    {
        $this->authorize('update', ContractedDriver::class);

        $driver = ContractedDriver::findOrFail($id);
        $validated = $request->validated();

        $this->driverService->update($driver, $validated);

        return redirect()
            ->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver updated successfully.');
    }

    /**
     * Deactivate a contracted driver.
     */
    public function deactivate($id)
    {
        $driver = ContractedDriver::findOrFail($id);

        $driver->update([
            'IsActive' => 0,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()
            ->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver deactivated.');
    }

    /**
     * Remove the specified contracted driver from storage.
     */
    public function destroy($id)
    {
        $this->authorize('delete', ContractedDriver::class);

        $driver = ContractedDriver::findOrFail($id);
        $this->driverService->delete($driver);

        return redirect()
            ->route('fleet.contracted_drivers.index')
            ->with('success', 'Contracted driver deleted successfully.');
    }

    /**
     * Display the specified contracted driver details.
     */
    public function show($id)
    {
        $this->authorize('view', ContractedDriver::class);

        // Load contracted driver with company and trips
        $driver = ContractedDriver::with([
            'company',
            'tripLogs.vehicle'
        ])->findOrFail($id);

        // Fetch all trip logs tied to this contracted driver
        $trips = FleetTripLog::with(['vehicle', 'creator'])
            ->where('DriverNo', $id)
            ->orderByDesc('TripDate')
            ->get();

        // Get active vehicles (for possible reassignment, etc.)
        $activeStatusId = CodeDetail::where('CodeID', 'VehicleStatus')
            ->where('Description', 'Active')
            ->value('Id');

        $vehicles = FleetVehicle::where('Status', $activeStatusId)->get();

        // Get assigners (if you use employees for logging trips)
        $assigners = Employee::select(DB::raw("CONCAT(LastName, ' ', FirstName) AS name"), 'Id')
            ->pluck('name', 'Id');

        return view('fleet.contracted_drivers.show', compact(
            'driver',
            'trips',
            'vehicles',
            'assigners'
        ));
    }
}
