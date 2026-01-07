<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetVehicleAssignment;
use App\Models\Fleet\Branch;
use App\Models\Fleet\FuelType;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\FleetModel;
use App\Http\Requests\FleetManagement\VehicleManagementRequest;
use App\Services\FleetManagement\VehicleManagementService;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetDriverAssignment;
use App\Models\Fleet\FleetContractedDriverAssignment;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetInsuranceTracker;
use App\Models\Fleet\FleetInspectionSchedule;
use App\Models\Fleet\FleetMaintenanceSchedule;
use App\Models\Fleet\FleetRepairLog;
use Illuminate\Support\Collection;

class VehicleController extends Controller
{
    protected VehicleManagementService $vehicleService;

    public function __construct(VehicleManagementService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    /**
     * Display a list of vehicles.
     */
    public function index()
    {
        $this->authorize('viewAny', FleetVehicle::class);

        $vehicles = FleetVehicle::with(['vehicleType', 'fuelType', 'branch'])
            ->get();

        return view('fleet.vehicles.index', compact('vehicles'));
    }

    /**
     * Show create vehicle form.
     */
    public function create()
    {
        $this->authorize('create', FleetVehicle::class);
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
        $fuelTypes = FuelType::where('IsActive' , '1')->get();
        $branches = Branch::all();
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();
        $vehicleStatuses = CodeDetail::where('CodeID', 'VehicleStatus')->orderBy('Value')->get();

        return view('fleet.vehicles.create', compact(
            'vehicleTypes', 'fuelTypes', 'branches', 'brands', 'fleetModels', 'vehicleStatuses'
        ));
    }

    /**
     * Store a new vehicle.
     */
    public function store(VehicleManagementRequest $request)
    {
        $this->authorize('create', FleetVehicle::class);

        $validated = $request->validated();
        $imageFile = $request->file('ImageFile');

        // Automatically set the vehicle status to "Active"
        $activeStatus = CodeDetail::where('CodeID', 'VehicleStatus')
            ->where('Description', 'Active')
            ->value('ID');

        if ($activeStatus) {
            $validated['Status'] = $activeStatus;
        }

        $vehicle = $this->vehicleService->create($validated, $imageFile);

        return redirect()->route('fleet.vehicles.index')
            ->with('success', 'Vehicle registered successfully and set to Active.');
    }

    /**
     * Show vehicle details.
     */
    public function show($id)
    {
        $vehicle = FleetVehicle::with([
            'vehicleType', 
            'fuelType', 
            'branch', 
            'brand', 
            'model',
            'vehicleStatus'
        ])->findOrFail($id);

        $this->authorize('view', $vehicle);

        // Get assignments for this vehicle
        $assignments = FleetVehicleAssignment::where('VehicleID', $vehicle->Id)
            ->with(['trip' => function($query) {
                $query->with(['parentTripType', 'parentVehicleType', 'statusDetail']);
            }])
            ->get();

        // Extract trips from assignments
        $trips = $assignments->pluck('trip')->filter()->unique('Id');

        // Get driver assignments for this vehicle
        $driverList = $this->getVehicleDrivers($vehicle->Id);
        
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
        $fuelTypes = FuelType::all();
        $branches = Branch::all();
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();
        
        // Get other related records
        $insuranceRecords = FleetInsuranceTracker::with(['insurance', 'insuranceStatus'])
            ->where('VehicleID', $vehicle->Id)
            ->orderByDesc('CoverageEndDate')
            ->get();
            
        $inspections = FleetInspectionSchedule::with(['inspectionStatus', 'inspector'])
            ->where('VehicleID', $vehicle->Id)
            ->orderByDesc('InspectionDate')
            ->get();
            
        $maintenanceLogs = FleetMaintenanceSchedule::with(['maintenanceType', 'maintenanceStatus'])
            ->where('VehicleID', $vehicle->Id)
            ->orderByDesc('ScheduledDate')
            ->get();
            
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
            'driverList', 
            'insuranceRecords', 
            'inspections', 
            'repairLogs', 
            'maintenanceLogs', 
            'trips'
        ));
    }

    /**
     * Merge trips and assignments to get all drivers with period.
     */
    private function getVehicleDrivers(int $vehicleId)
    {
        // Permanent assignments
        $assignedDrivers = FleetDriverAssignment::where('VehicleID', $vehicleId)
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

        // Contracted assignments
        $contractedDrivers = FleetContractedDriverAssignment::where('VehicleID', $vehicleId)
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

        // Merge all and attach driver names
        $allDrivers = $assignedDrivers->concat($contractedDrivers)->filter();

        $driverIds = $allDrivers->pluck('Id')->unique()->filter();

        $permanentDrivers = FleetDriver::whereIn('Id', $driverIds)->get()->keyBy('Id');
        $contractedDrivers = ContractedDriver::whereIn('Id', $driverIds)->get()->keyBy('Id');

        return $allDrivers->map(function ($item) use ($permanentDrivers, $contractedDrivers) {
            $driver = $permanentDrivers->get($item['Id']) ?? $contractedDrivers->get($item['Id']);
            return [
                'Id' => $item['Id'],
                'Name' => $driver?->FullName,
                'DriverType' => $item['DriverType'],
                'Source' => $item['Source'],
                'Period' => $item['Period'],
            ];
        });
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $vehicle = FleetVehicle::findOrFail($id);
        $this->authorize('update', $vehicle);

        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
        $fuelTypes = FuelType::all();
        $branches = Branch::all();
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();
        $vehicleStatuses = CodeDetail::where('CodeID', 'VehicleStatus')->orderBy('Value')->get();

        return view('fleet.vehicles.edit', compact(
            'vehicle', 'vehicleTypes', 'fuelTypes', 'branches', 'brands', 'fleetModels', 'vehicleStatuses'
        ));
    }

    /**
     * Update vehicle.
     */
    public function update(VehicleManagementRequest $request, $id)
    {
        $vehicle = FleetVehicle::findOrFail($id);
        $this->authorize('update', $vehicle);

        $validated = $request->validated();
        $imageFile = $request->file('ImageFile');

        $this->vehicleService->update($vehicle, $validated, $imageFile);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function deactivate($id)
    {
        $vehicle = FleetVehicle::findOrFail($id);
        $this->authorize('update', $vehicle);

        $vehicle->update([
            'IsActive' => 0,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle deregistered successfully.');
    }

    /**
     * Delete vehicle.
     */
    public function destroy($id)
    {
        $vehicle = FleetVehicle::findOrFail($id);
        $this->authorize('delete', $vehicle);

        $this->vehicleService->delete($vehicle);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }

    /**
     * Get models by brand for dependent dropdown.
     */
    public function getByMake($Id)
    {
        $models = FleetModel::where('BrandID', $Id)->get();
        return response()->json($models);
    }
}