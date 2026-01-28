<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\FleetManagement\FleetModelRequest;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\FleetModel;
use App\Services\FleetManagement\FleetModelService;

class FleetModelController extends Controller
{
    protected FleetModelService $service;

    public function __construct(FleetModelService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', FleetModel::class);
        $fleetModels = FleetModel::withCount('vehicles')->get();
        $brands = FleetMake::all();

        return view('fleetmanagement.fleetmodel.index', compact('fleetModels', 'brands'));
    }

    public function create()
    {
        $this->authorize('create', FleetModel::class);

        $brands = FleetMake::all();

        return view('fleetmanagement.fleetmodel.create', compact('brands'));
    }

    public function store(FleetModelRequest $request)
    {
        $this->authorize('create', FleetModel::class);
        $validated = $request->validated();

        try {
            $fleetModel = $this->service->create($validated);

            return redirect()
                ->route("fleetmodel.index")
                ->with('success', $fleetModel->wasRecentlyCreated
                    ? 'Fleet Model created successfully.'
                    : 'Fleet Model restored successfully.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['ModelName' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->authorize('view', FleetModel::class);
        $fleetModel = FleetModel::findOrFail($id);
        $brands = FleetMake::all();

        return view('fleetmanagement.fleetmodel.show', compact('fleetModel', 'brands'));
    }

    public function update(FleetModelRequest $request, $id)
    {
        $this->authorize('update', FleetModel::class);
        $fleetModel = FleetModel::findOrFail($id);
        $brands = FleetMake::all();
        $fleetModel->update($request->validated());


        return redirect()
            ->route('fleetmodel.index')
            ->with('success', 'Fleet Model updated successfully.');
    }

    public function edit($id)
    {
        $this->authorize('edit', FleetModel::class);
        $brands = FleetMake::all();
        $fleetModel = FleetModel::findOrFail($id);

        return view('fleetmanagement.fleetmodel.edit', compact('fleetModel', 'brands'));
    }

    public function destroy(string $Id)
    {
        $this->authorize('destroy', FleetModel::class);
        $model = FleetModel::findOrFail($Id);

        $this->service->delete($model);

        return redirect()->route('fleetmodel.index')->with('success', 'Model deleted successfully.');
    }
}
