<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetVehicleInspectionRequest;
use App\Models\Fleet\FleetVehicleInspection;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FuelType;
use App\Services\FleetManagement\FleetVehicleInspectionService;
use Illuminate\Support\Facades\Auth;
use App\Services\DMS\DocumentService;


class FleetVehicleInspectionController extends Controller
{
    protected FleetVehicleInspectionService $inspectionService;

    public function __construct(FleetVehicleInspectionService $inspectionService)
    {
        $this->inspectionService = $inspectionService;
    }

    public function index()
    {
        $this->authorize('viewAny', FleetVehicleInspection::class);
        $inspections = FleetVehicleInspection::with(['vehicle', 'driver', 'fuel', 'postTrips'])
            ->where('CreatedBy', Auth::id())
            ->whereNull('ParentInspectionID') 
            ->get();

        return view('fleet.vehicle_inspection.index', compact('inspections'));
    }

    public function create()
    {
        $this->authorize('create', FleetVehicleInspection::class);
        $vehicles = FleetVehicle::all();
        $drivers  = FleetDriver::all();
        $fuels    = FuelType::all();

        return view('fleet.vehicle_inspection.create', compact('vehicles', 'drivers', 'fuels'));
    }

    public function createPostTrip($id)
    {
        $this->authorize('create', FleetVehicleInspection::class);
        $parentInspection = FleetVehicleInspection::findOrFail($id);
        $vehicles = FleetVehicle::all();
        $drivers  = FleetDriver::all();
        $fuels    = FuelType::all();

        return view('fleet.vehicle_inspection.create', [
            'vehicles'          => $vehicles,
            'drivers'           => $drivers,
            'fuels'             => $fuels,
            'parentInspection'  => $parentInspection,
        ]);
    }

    public function store(FleetVehicleInspectionRequest $request)
{
    $this->authorize('create', FleetVehicleInspection::class);

    $document = $request->file('Document');

    $this->inspectionService->create($request->validated(), $document);

    return redirect()->route('fleet.vehicle_inspection.index')
        ->with('success', 'Vehicle inspection created successfully.');
}


    public function show($id)
    {
        $this->authorize('view', FleetVehicleInspection::class);
        $inspection = FleetVehicleInspection::with(['vehicle', 'driver', 'fuel', 'postTrips'])->findOrFail($id);
        return view('fleet.vehicle_inspection.show', compact('inspection'));
    }

    public function edit($id)
    {
        $this->authorize('edit', FleetVehicleInspection::class);
        $inspection = FleetVehicleInspection::findOrFail($id);
        $vehicles   = FleetVehicle::all();
        $drivers    = FleetDriver::all();
        $fuels      = FuelType::all();

        return view('fleet.vehicle_inspection.edit', compact('inspection', 'vehicles', 'drivers', 'fuels'));
    }

    public function update(FleetVehicleInspectionRequest $request, $id)
    {
        $this->authorize('update', FleetVehicleInspection::class);
        $inspection = FleetVehicleInspection::findOrFail($id);
        $this->inspectionService->update($inspection, $request->validated());

        return redirect()->route('fleet.vehicle_inspection.index')
            ->with('success', 'Vehicle inspection updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('destroy', FleetVehicleInspection::class);
        $inspection = FleetVehicleInspection::findOrFail($id);
        $this->inspectionService->delete($inspection);

        return redirect()->route('fleet.vehicle_inspection.index')
            ->with('success', 'Vehicle inspection deleted successfully.');
    }
}
