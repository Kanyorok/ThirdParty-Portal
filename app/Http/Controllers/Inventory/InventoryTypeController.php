<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryType;
use App\Http\Requests\Inventory\InventoryTypeRequest;
use App\Services\Inventory\InventoryTypeService;

class InventoryTypeController extends Controller
{
    protected InventoryTypeService $service;

    public function __construct(InventoryTypeService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $types = InventoryType::all();
        return view('inventory.itemmaster.inventorytype.index', compact('types'));
    }

    public function create()
    {
        $this->authorize('create', InventoryType::class);

        return view('inventory.itemmaster.inventorytype.create');
    }

    public function store(InventoryTypeRequest $request)
    {
        $this->authorize('create', InventoryType::class);

        $this->service->create($request->validated());

        return redirect()->route('inventorytype.index')->with('success', 'Inventory type created successfully.');
    }

    public function edit($id)
    {
        $type = InventoryType::findOrFail($id);
        $this->authorize('update', $type);
        return view('inventory.itemmaster.inventorytype.edit', compact('type'));
    }

    public function update(InventoryTypeRequest $request, $id)
    {
        $type = InventoryType::findOrFail($id);
        $this->authorize('update', $type);

        $this->service->update($type, $request->validated());

        return redirect()->route('inventorytype.index')->with('success', 'Inventory type updated successfully.');
    }

    public function destroy($id)
    {
        $type = InventoryType::findOrFail($id);
        $this->authorize('destroy', $type);

        $this->service->delete($type);

        return redirect()->route('inventorytype.index')->with('success', 'Inventory type deleted successfully.');
    }
}
