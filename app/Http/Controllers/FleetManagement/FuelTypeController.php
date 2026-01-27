<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FuelTypRequest;
use App\Models\Fleet\FuelType;
use App\Services\FleetManagement\FuelTypeService;

class FuelTypeController extends Controller
{
    protected FuelTypeService $service;

    public function __construct(FuelTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fuelTypes = FuelType::withCount('vehicles')->get();

        return view('fleetmanagement.fueltypes.index', compact('fuelTypes'));
    }

    public function create()
    {
        return view('fleetmanagement.fueltypes.create');
    }

    public function store(FuelTypRequest $request)
    {
        $validated = $request->validated();

        $fuelType = $this->service->create($validated);

        return redirect()->route('fueltypes.index')->with('success', 'Fuel type created successfully.');
    }

    public function edit($id)
    {
        $fuelType = FuelType::findOrFail($id);

        return view('fleetmanagement.fueltypes.edit', compact('fuelType'));
    }

    public function update(FuelTypRequest $request, $id)
    {
        $fuelType = FuelType::findOrFail($id);
        $validated = $request->validated();
        $fuelType = $this->service->update($fuelType, $validated);

        return redirect()->route('fueltypes.index')->with('success', 'Fuel type updated successfully.');
    }

    public function destroy($id)
    {

        $fuelType = FuelType::findOrFail($id);
        $this->service->delete($fuelType);

        return redirect()->route('fueltypes.index')->with('success', 'Fuel type deleted successfully.');
    }

    public function show($id)
    {
        $fuelType = FuelType::findOrFail($id);

        return view('fleetmanagement.fueltypes.show', compact('fuelType'));
    }
}
