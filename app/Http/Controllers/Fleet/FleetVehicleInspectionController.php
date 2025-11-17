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
use App\Models\Core\CodeDetail;

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

        $inspections = FleetVehicleInspection::with(['vehicle', 'driver', 'engineOil', 'postTrips', 'fuel', 'coolant', 'inspectionType'])
            ->where('CreatedBy', Auth::id())
            ->whereNull('ParentInspectionID')
            ->get();

        return view('fleet.vehicle_inspection.index', compact('inspections'));
    }

    public function create()
    {
        $this->authorize('create', FleetVehicleInspection::class);

        $vehicles = FleetVehicle::all();
        $fuels    = FuelType::where('IsActive', true)->get();
        $inspectionTypes = CodeDetail::where('CodeID', 'InspectionType')->get();
        $engineOilUOMs = CodeDetail::where('CodeID', 'FleetUOM')->get();
        $fuelUOMs = CodeDetail::where('CodeID', 'FuelUOM')->get();
        $coolantUOMs = CodeDetail::where('CodeID', 'FleetUOM')->get();

        return view('fleet.vehicle_inspection.create', compact('vehicles', 'fuels', 'inspectionTypes', 'engineOilUOMs', 'fuelUOMs', 'coolantUOMs'));
    }

    public function createPostTrip($id)
    {
        $this->authorize('create', FleetVehicleInspection::class);

        // Eager-load inspectionType so ID is available in the Blade
        $parentInspection = FleetVehicleInspection::with('inspectionType')->findOrFail($id);

        $vehicles = FleetVehicle::all();
        $fuels    = FuelType::where('IsActive', true)->get();
        $inspectionTypes = CodeDetail::where('CodeID', 'InspectionType')->get();
        $engineOilUOMs = CodeDetail::where('CodeID', 'FleetUOM')->get();
        $fuelUOMs = CodeDetail::where('CodeID', 'FuelUOM')->get();
        $coolantUOMs = CodeDetail::where('CodeID', 'FleetUOM')->get();

        return view('fleet.vehicle_inspection.create', [
            'fuels'             => $fuels,
            'vehicles'          => $vehicles,
            'engineOilUOMs'     => $engineOilUOMs,
            'fuelUOMs'          => $fuelUOMs,
            'coolantUOMs'      => $coolantUOMs, 
            'fuels'             => $fuels,
            'inspectionTypes'   => $inspectionTypes,
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

        $inspection = FleetVehicleInspection::with(['vehicle', 'driver', 'fuel', 'postTrips', 'inspectionType'])
            ->findOrFail($id);

        return view('fleet.vehicle_inspection.show', compact('inspection'));
    }

    public function edit($id)
    {
        $this->authorize('edit', FleetVehicleInspection::class);
        $fuels    = FuelType::where('IsActive', true)->get();
        $inspection = FleetVehicleInspection::findOrFail($id);
        $vehicles   = FleetVehicle::all();
        $drivers    = FleetDriver::all();
        $inspectionTypes = CodeDetail::where('CodeID', 'InspectionType')->get();
        $engineOilUOMs = CodeDetail::where('CodeID', 'FleetUOM')->get();
        $fuelUOMs = CodeDetail::where('CodeID', 'FuelUOM')->get();
        $coolantUOMs = CodeDetail::where('CodeID', 'FleetUOM')->get();


        return view('fleet.vehicle_inspection.edit', compact('inspection', 'vehicles', 'drivers', 'engineOilUOMs', 'fuelUOMs', 'coolantUOMs', 'inspectionTypes','fuels'));
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

    public function getVehicleDriver($Id)
    {
        $vehicle = FleetVehicle::with('fuelType')->find($Id);

        $driverAssignment = \App\Models\Fleet\FleetDriverAssignment::where('VehicleID', $Id)
            ->whereNull('DeletedOn')
            ->latest('AssignmentDate')
            ->first();

        if (!$driverAssignment) {
            $driverAssignment = \App\Models\Fleet\FleetContractedDriverAssignment::where('VehicleID', $Id)
                ->whereNull('DeletedOn')
                ->latest('AssignmentDate')
                ->first();
        }

        return response()->json([
            'driverId'      => $driverAssignment?->DriverID,
            'driverName'    => $driverAssignment?->driver?->FullName ?? 'No driver assigned',
            'fuelTypeId'    => $vehicle?->fuelType?->Id,
            'fuelTypeName'  => $vehicle?->fuelType?->FuelName,
        ]);
    }

     public function getLastMileage($vehicleId)
    {
        $lastInspection = \App\Models\Fleet\FleetVehicleInspection::where('VehicleID', $vehicleId)
            ->orderByDesc('InspectionDate')
            ->first();

        return response()->json([
            'lastMileage' => $lastInspection?->Mileage ?? 0,
        ]);
    }

}
