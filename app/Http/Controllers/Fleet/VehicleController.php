<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\Core\CodeDetail;
use App\Models\Fleet\Branch;
use App\Models\Fleet\FleetVehicleAssignment;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\FleetModel;
use App\Models\Fleet\FuelType;
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
        $vehicles = FleetVehicle::with(['vehicleType', 'fuelType', 'branch'])
            ->where('CreatedBy', Auth::id())
            ->get();

        return view('fleet.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
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
        $validated = $request->validated();

        $vehicle = $this->vehicleService->create($validated);

        // Optional assignment history
        
        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle registered successfully.');
    }

    public function show($id)
    {
        $vehicle = FleetVehicle::with(['vehicleType', 'fuelType', 'branch', 'brand', 'model'])->findOrFail($id);
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')->orderBy('Value')->get();
        $fuelTypes = FuelType::all();
        $branches = Branch::all();
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();

        return view('fleet.vehicles.show', compact('vehicle', 'vehicleTypes', 'fuelTypes', 'branches', 'brands', 'fleetModels'));
    }

    public function edit($id)
    {
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
        $vehicle = FleetVehicle::findOrFail($id);
        $this->vehicleService->delete($vehicle);

        return redirect()->route('fleet.vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}
