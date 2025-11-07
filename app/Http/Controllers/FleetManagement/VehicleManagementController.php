<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;
use App\Models\FleetManagement\FleetMake; 
use App\Models\FleetManagement\FleetModel;
use App\Models\FleetManagement\VehicleRegistry;
use App\Policies\FleetManagement\VehicleRegistryPolicy;
use App\Models\Core\CodeDetail;
use App\Http\Requests\FleetManagement\VehicleRegistryRequest;
use App\Services\FleetManagement\VehicleRegistryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class VehicleManagementController extends Controller
{
    protected VehicleRegistryService $service;

    public function __construct(VehicleRegistryService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', VehicleRegistry::class);
        $fleetModels = FleetModel::all();
        $brands = FleetMake::all();
        $vehicleTypes = CodeDetail::all();
        $vehicles = VehicleRegistry::all();
        return view('fleetmanagement.registry.index', compact('fleetModels', 'brands', 'vehicleTypes', 'vehicles'));
    }

    public function create()
    {
        $this->authorize('create', VehicleRegistry::class);
        $brands = FleetMake::all();
        $fleetModels = FleetModel::all();
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')
        ->orderBy('Value')
        ->get();
        return view('fleetmanagement.registry.create', compact('brands', 'fleetModels','vehicleTypes'));
    }


    public function store(VehicleRegistryRequest $request)
    {
        $this->authorize('create', VehicleRegistry::class);
        $validated = $request->validated();
        $vehicle = $this->service->create($validated);
        $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')
            ->orderBy('Value')
            ->get();
        return redirect()
            ->route("vehicle-registry.index")
            ->with('success', 'Vehicle created successfully.');
        }


    public function show($id)
        {
            $this->authorize('view', VehicleRegistry::class);
            $vehicle = VehicleRegistry::findOrFail($id);
            $fleetModel=FleetModel::all();
            $brands = FleetMake::all();
            $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')
            ->orderBy('Value')
            ->get();
            return view('fleetmanagement.registry.show', compact('fleetModel', 'brands', 'vehicle'));
        }

    public function edit($id)
        {
            $this->authorize('update', VehicleRegistry::class);
            $brands = FleetMake::all();
            $fleetModel = FleetModel::all();
            $vehicle=VehicleRegistry::findOrFail($id);
            $vehicleTypes = CodeDetail::where('CodeID', 'VehicleType')
            ->orderBy('Value')
            ->get();
            return view('fleetmanagement.registry.edit', compact('fleetModel', 'brands','vehicle','vehicleTypes'));
        }

   public function update(VehicleRegistryRequest $request, $id)
{
    $this->authorize('update', VehicleRegistry::class);
    $vehicle = VehicleRegistry::findOrFail($id);

    $this->service->update($vehicle, $request->validated());

    return redirect()
        ->route('vehicle-registry.index')
        ->with('success', 'Vehicle updated successfully.');
}


    public function destroy(string $Id)
    {
        $this->authorize('destroy', VehicleRegistry::class);
        $vehicle = VehicleRegistry::findOrFail($Id);

            $this->service->delete($vehicle);

            return redirect()->route('vehicle-registry.index')->with('success', 'Vehicle deleted successfully.');
        }


    }
