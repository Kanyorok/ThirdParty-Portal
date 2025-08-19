<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetTripLogRequest;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriver;
use App\Models\Core\CodeDetail;
use Illuminate\Support\Facades\DB;
use App\Models\Fleet\ContractedDriver;
use App\Services\FleetManagement\FleetTripLogService;

class FleetTripLogController extends Controller
{
    protected FleetTripLogService $tripLogService;

    public function __construct(FleetTripLogService $tripLogService)
    {
        $this->tripLogService = $tripLogService;
    }

    public function index()
    {
        $tripLogs = FleetTripLog::with(['vehicle', 'driverType', 'driverPermanent', 'driverContracted'])
            ->orderByDesc('CreatedOn')
            ->get();

        return view('fleet.trip_logs.index', compact('tripLogs'));
    }

    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $drivers = FleetDriver::where('IsActive', 1)->get();
        $driverTypes = CodeDetail::where('CodeID', 'DriverType')
            ->orderBy('Value')
            ->get();
        $contractedDrivers = ContractedDriver::where('IsActive', 1)->get();

        return view('fleet.trip_logs.create', compact('vehicles', 'drivers', 'contractedDrivers','driverTypes'));
    }

    public function store(FleetTripLogRequest $request)
    {
        $this->tripLogService->createTrip($request->validated());

        return redirect()
            ->route('fleet.trip_logs.index')
            ->with('success', 'Trip logged successfully.');
    }

    public function show($id)
    {
        $tripLogs = FleetTripLog::with(['vehicle', 'driverType', 'driverPermanent', 'driverContracted'])
            ->findOrFail($id);

        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $drivers = FleetDriver::where('IsActive', 1)->get();
        $driverTypes = CodeDetail::where('CodeID', 'DriverType')
            ->orderBy('Value')
            ->get();
        $contractedDrivers = ContractedDriver::where('IsActive', 1)->get();

        return view('fleet.trip_logs.show', compact('vehicles','tripLogs', 'drivers', 'contractedDrivers','driverTypes'));
    }

    public function edit($id)
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $drivers = FleetDriver::where('IsActive', 1)->get();
        $driverTypes = CodeDetail::where('CodeID', 'DriverType')
            ->orderBy('Value')
            ->get();
        $contractedDrivers = ContractedDriver::where('IsActive', 1)->get();
        return view('fleet.vehicles.edit', compact('vehicles', 'drivers', 'contractedDrivers','driverTypes'));
    }

    public function update(FleetTripLogRequest $request, $id)
    {
        $tripLogs = FleetTripLog::with(['vehicle', 'driverType', 'driverPermanent', 'driverContracted'])
            ->findOrFail($id);

        $validated = $request->validated();

        $this->tripLogService->updateTrip($tripLogs, $validated);

        return redirect()->route('fleet.trip_logs.index')->with('success', 'Trip updated successfully.');
    }

    public function destroy($id)
    {
        $tripLogs = FleetTripLog::with(['vehicle', 'driverType', 'driverPermanent', 'driverContracted'])
            ->findOrFail($id);

        $this->tripLogService->deleteTrip($tripLogs);

        return redirect()->route('fleet.trip_logs.index')->with('success', 'Trip deleted successfully.');
    }
}
