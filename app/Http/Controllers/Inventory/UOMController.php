<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\UnitOfMeasureRequest;
use App\Models\Inventory\UnitOfMeasure;
use App\Services\Inventory\UnitOfMeasureService;

class UOMController extends Controller
{
    protected $service;

    public function __construct(UnitOfMeasureService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $units = UnitOfMeasure::all();

        return view('inventory.itemmaster.unitofmeasure.index', compact('units'));
    }

    public function create()
    {

        $this->authorize('create', UnitOfMeasure::class);

        return view('inventory.itemmaster.unitofmeasure.create');
    }

    public function store(UnitOfMeasureRequest $request)
    {
        $this->authorize('create', UnitOfMeasure::class);
        $this->service->create($request->validated());

        return redirect()->route('unitofmeasure.index')->with('success', 'Unit of Measure created successfully.');
    }

    public function show($Id)
    {
        $unit = UnitOfMeasure::findOrFail($Id);

        return response()->json($unit);
    }

    public function edit($Id)
    {
        $unit = UnitOfMeasure::findOrFail($Id);
        $this->authorize('update', UnitOfMeasure::class);

        return response()->json($unit);
    }

    public function update(UnitOfMeasureRequest $request, $Id)
    {
        $unit = UnitOfMeasure::findOrFail($Id);
        $this->authorize('update', UnitOfMeasure::class);
        $this->service->update($unit, $request->validated());

        return redirect()->route('unitofmeasure.index')->with('success', 'Unit of Measure updated successfully.');
    }

    public function destroy($Id)
    {
        $unit = UnitOfMeasure::findOrFail($Id);
        $this->authorize('destroy', UnitOfMeasure::class);
        $this->service->delete($unit);

        return redirect()->route('unitofmeasure.index')->with('success', 'Unit of Measure deleted successfully.');
    }
}
