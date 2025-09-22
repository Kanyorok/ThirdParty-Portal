<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Policies\FleetManagement\FleetVehiclePolicy;
use App\Models\Core\CodeDetail;
use App\Models\Fleet\Branch;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetInsuranceTracker;
use App\Models\Fleet\FleetRepairLog;
use App\Models\Fleet\FleetMaintenanceSchedule;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicleAssignment;
use App\Models\FleetManagement\FleetMake;
use App\Models\Fleet\FleetDriverAssignment;
use App\Models\Fleet\FleetContractedDriverAssignment;
use App\Models\FleetManagement\FleetModel;
use App\Models\Fleet\FuelType;
use App\Models\Fleet\FleetInspectionSchedule;
use App\Http\Requests\FleetManagement\VehicleManagementRequest;
use App\Services\FleetManagement\VehicleManagementService;

class VehicleController extends Controller
{
    protected VehicleManagementService $vehicleService;

    public function __construct(VehicleManagementService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function index()
    {
        $this->authorize('viewAny', FleetVehicle::class);
        $vehicles = FleetVehicle::with(['vehicleType', 'fuelType', 'branch'])
            ->where('CreatedBy', Auth::id())
            ->get();

        return view('fleet.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $this->authorize('create', FleetVehicle::class);
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
        $fuelTypes = FuelType::all();
        $branches = Branch::all();
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();
        $vehicleStatuses = CodeDetail::where('CodeID', 'VehicleStatus')->orderBy('Value')->get();

        return view('fleet.vehicles.create', compact('vehicleTypes', 'fuelTypes', 'branches', 'brands', 'fleetModels', 'vehicleStatuses'));
    }

    public function store(VehicleManagementRequest $request)
    {
        $this->authorize('update', FleetVehicle::class);
        $validated = $request->validated();

        $vehicle = $this->vehicleService->create($validated);

        // Optional assignment history

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle registered successfully.');
    }

    public function show($id)
    {
        $vehicle = FleetVehicle::with(['vehicleType', 'fuelType', 'branch', 'brand', 'model'])
            ->findOrFail($id);

        // Get trips assigned to this vehicle
        $trips = FleetTripLog::with(['driverContracted', 'driverPermanent', 'driverType'])
            ->where('VehicleID', $vehicle->Id)
            ->orderByDesc('TripStartDate')
            ->get();

        // Trip drivers with period
        $tripDrivers = FleetTripLog::with('driverType')
            ->where('VehicleID', $vehicle->Id)
            ->get(['DriverID', 'DriverType', 'TripStartDate', 'TripEndDate'])
            ->map(function ($trip) {
                return [
                    'Id' => $trip->DriverID,
                    'DriverType' => $trip->driverType ? $trip->driverType->Description : null,
                    'Source' => 'TripLog',
                    'Period' => $trip->TripStartDate
                        ? \Carbon\Carbon::parse($trip->TripStartDate)->format('d/m/Y') .
                        ' → ' .
                        ($trip->TripEndDate ? \Carbon\Carbon::parse($trip->TripEndDate)->format('d/m/Y') : '—')
                        : null,
                ];
            });

        // Permanent assignments with Assignment/Unassignment
        $assignedDrivers = FleetDriverAssignment::where('VehicleID', $vehicle->Id)
            ->get(['DriverID', 'AssignmentDate', 'UnassignmentDate'])
            ->map(function ($assignment) {
                return [
                    'Id' => $assignment->DriverID,
                    'DriverType' => 'Permanent Driver',
                    'Source' => 'Assignment',
                    'Period' => $assignment->AssignmentDate
                        ? \Carbon\Carbon::parse($assignment->AssignmentDate)->format('d/m/Y') .
                        ' → ' .
                        ($assignment->UnassignmentDate ? \Carbon\Carbon::parse($assignment->UnassignmentDate)->format('d/m/Y') : '—')
                        : null,
                ];
            });

        // Contracted assignments with Assignment/Unassignment
        $contractedDrivers = FleetContractedDriverAssignment::where('VehicleID', $vehicle->Id)
            ->get(['DriverID', 'AssignmentDate', 'UnassignmentDate'])
            ->map(function ($assignment) {
                return [
                    'Id' => $assignment->DriverID,
                    'DriverType' => 'Contracted Driver',
                    'Source' => 'ContractedAssignment',
                    'Period' => $assignment->AssignmentDate
                        ? \Carbon\Carbon::parse($assignment->AssignmentDate)->format('d/m/Y') .
                        ' → ' .
                        ($assignment->UnassignmentDate ? \Carbon\Carbon::parse($assignment->UnassignmentDate)->format('d/m/Y') : '—')
                        : null,
                ];
            });

        // Merge all driver sources
        $allDrivers = $tripDrivers->merge($assignedDrivers)->merge($contractedDrivers)->filter();

        $driverIds = $allDrivers->pluck('Id')->unique()->filter();
        $permanentDrivers = FleetDriver::whereIn('Id', $driverIds)->get()->keyBy('Id');
        $contractedDrivers = ContractedDriver::whereIn('Id', $driverIds)->get()->keyBy('Id');

        // Attach driver details
        $driverList = $allDrivers->map(function ($item) use ($permanentDrivers, $contractedDrivers) {
            $driver = $permanentDrivers->get($item['Id']) ?? $contractedDrivers->get($item['Id']);
            return [
                'Id' => $item['Id'],
                'Name' => $driver ? $driver->FullName : null,
                'DriverType' => $item['DriverType'],
                'Source' => $item['Source'],
                'Period' => $item['Period'],
            ];
        });

        // Other lookups
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
        $fuelTypes = FuelType::all();
        $branches = Branch::all();
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();

        // Fetch insurance records
        $insuranceRecords = FleetInsuranceTracker::with(['insurance', 'insuranceStatus'])
            ->where('VehicleID', $vehicle->Id)
            ->orderByDesc('CoverageEndDate')
            ->get();

        // Fetch inspections
        $inspections = FleetInspectionSchedule::with(['inspectionStatus', 'inspector'])
            ->where('VehicleID', $vehicle->Id)
            ->orderByDesc('InspectionDate')
            ->get();

        // Fetch maintenance schedules
        $maintenanceLogs = FleetMaintenanceSchedule::with(['maintenanceType', 'maintenanceStatus'])
            ->where('VehicleID', $vehicle->Id)
            ->orderByDesc('ScheduledDate')
            ->get();

        // Fetch repair logs
        $repairLogs = FleetRepairLog::with(['repairType', 'schedule'])
            ->where('VehicleID', $vehicle->Id)
            ->orderByDesc('RepairDate')
            ->get();

        return view('fleet.vehicles.show', compact(
            'vehicle',
            'vehicleTypes',
            'fuelTypes',
            'branches',
            'brands',
            'fleetModels',
            'trips',
            'driverList',
            'insuranceRecords',
            'inspections',
            'repairLogs',
            'maintenanceLogs'
        ));
    }


    public function edit($id)
    {
        $this->authorize('edit', FleetVehicle::class);
        $vehicle = FleetVehicle::findOrFail($id);
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
        $fuelTypes = FuelType::all();
        $branches = Branch::all();
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();
        $vehicleStatuses = CodeDetail::where('CodeID', 'VehicleStatus')->orderBy('Value')->get();

        return view('fleet.vehicles.edit', compact('vehicle', 'vehicleTypes', 'fuelTypes', 'branches', 'brands', 'fleetModels', 'vehicleStatuses'));
    }

    public function update(VehicleManagementRequest $request, $id)
    {
        $this->authorize('update', FleetVehicle::class);
        $vehicle = FleetVehicle::findOrFail($id);
        $validated = $request->validated();

        $this->vehicleService->update($vehicle, $validated);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }


    public function deactivate($id)
    {
        $vehicle = FleetVehicle::findOrFail($id);
        $vehicle->update([
            'IsActive' => 0,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle deregistered successfully.');
    }

    public function getByMake($Id)
    {
        $models = FleetModel::where('BrandID', $Id)->get();
        return response()->json($models);
    }


    public function destroy($id)
    {
        $this->authorize('destroy', FleetVehicle::class);
        $vehicle = FleetVehicle::findOrFail($id);
        $this->vehicleService->delete($vehicle);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}
