<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetTripLogRequest;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetRepairLog;
use App\Models\Fleet\FleetMaintenanceSchedule;
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
        $this->authorize('viewAny', FleetTripLog::class);
        $tripLogs = FleetTripLog::with(['vehicle', 'driverType', 'driverPermanent', 'driverContracted'])
            ->orderByDesc('CreatedOn')
            ->get();

        return view('fleet.trip_logs.index', compact('tripLogs'));
    }

    public function create()
    {
        $this->authorize('create', FleetTripLog::class);
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $drivers = FleetDriver::where('IsActive', 1)->get();
        $driverTypes = CodeDetail::where('CodeID', 'DriverType')
            ->orderBy('Value')
            ->get();
        $contractedDrivers = ContractedDriver::where('IsActive', 1)->get();

        return view('fleet.trip_logs.create', compact('vehicles', 'drivers', 'contractedDrivers', 'driverTypes'));
    }

    public function store(FleetTripLogRequest $request)
    {
        $this->authorize('create', FleetTripLog::class);
        $this->tripLogService->createTrip($request->validated());

        return redirect()
            ->route('fleet.trip_logs.index')
            ->with('success', 'Trip logged successfully.');
    }

    public function show($id)
    {
        $this->authorize('view', FleetTripLog::class);
        $tripLogs = FleetTripLog::with(['vehicle', 'driverType', 'driverPermanent', 'driverContracted'])
            ->findOrFail($id);

        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $drivers = FleetDriver::where('IsActive', 1)->get();
        $driverTypes = CodeDetail::where('CodeID', 'DriverType')
            ->orderBy('Value')
            ->get();
        $contractedDrivers = ContractedDriver::where('IsActive', 1)->get();

        return view('fleet.trip_logs.show', compact('vehicles', 'tripLogs', 'drivers', 'contractedDrivers', 'driverTypes'));
    }

    public function edit($id)
    {
        $this->authorize('edit', FleetTripLog::class);
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $drivers = FleetDriver::where('IsActive', 1)->get();
        $driverTypes = CodeDetail::where('CodeID', 'DriverType')
            ->orderBy('Value')
            ->get();
        $contractedDrivers = ContractedDriver::where('IsActive', 1)->get();
        return view('fleet.vehicles.edit', compact('vehicles', 'drivers', 'contractedDrivers', 'driverTypes'));
    }

    public function update(FleetTripLogRequest $request, $id, FleetTripLogService $tripLogService)
    {
        $this->authorize('update', FleetTripLog::class);
        $tripLog = FleetTripLog::findOrFail($id);

        $tripLogService->updateTrip($tripLog, $request->validated());

        return redirect()->route('fleet.trip_logs.index')
            ->with('success', 'Trip updated successfully.');
    }


    public function destroy($id)
    {
        $this->authorize('destroy', FleetTripLog::class);
        $tripLogs = FleetTripLog::with(['vehicle', 'driverType', 'driverPermanent', 'driverContracted'])
            ->findOrFail($id);

        $this->tripLogService->deleteTrip($tripLogs);

        return redirect()->route('fleet.trip_logs.index')->with('success', 'Trip deleted successfully.');
    }

   public function getAvailableVehicles()
{
    $startDate = request('start_date');
    $endDate   = request('end_date');

    if (!$startDate || !$endDate) {
        return response()->json([]);
    }

    $availableVehicles = FleetVehicle::where('IsActive', 1)
        ->whereDoesntHave('tripLogs', function ($q) use ($startDate, $endDate) {
            $q->where(function ($q2) use ($startDate, $endDate) {
                $q2->where('TripStartDate', '<', $endDate)
                   ->where('TripEndDate', '>', $startDate);
            });
        })
        ->whereDoesntHave('maintenanceSchedules', function ($q) use ($startDate, $endDate) {
            $q->where('Status', '!=', 0)
              ->where('ScheduledDate', '<=', $endDate)
              ->where('ScheduledDate', '>=', $startDate);
        })
        ->whereDoesntHave('repairLogs', function ($q) use ($startDate, $endDate) {
            $q->where('RepairDate', '<=', $endDate)
              ->where('RepairDate', '>=', $startDate);
        })
        ->get();

    return response()->json($availableVehicles);
}


public function getAvailablePermanentDrivers()
{
    $startDate = request('start_date');
    $endDate = request('end_date');

    if (!$startDate || !$endDate) {
        return response()->json([]);
    }

    $availableDrivers = FleetDriver::where('IsActive', 1)
        ->whereDoesntHave('tripLogs', function ($q) use ($startDate, $endDate) {
            $q->where(function ($q2) use ($startDate, $endDate) {
                $q2->where('TripStartDate', '<', $endDate)
                   ->where('TripEndDate', '>', $startDate);
            });
        })
        ->get();

    return response()->json($availableDrivers);
}

}
