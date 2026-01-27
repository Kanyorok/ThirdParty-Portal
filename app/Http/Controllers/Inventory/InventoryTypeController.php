<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryTypeRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryType;
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
        $types = InventoryType::with('type')->get();
        $inventoryTypes = CodeDetail::where('CodeID', 'InventoryTypeStatus')->get();

        return view('inventory.itemmaster.inventorytype.index', compact('types', 'inventoryTypes'));
    }

    public function create()
    {
        $this->authorize('create', InventoryType::class);
        $types = InventoryType::with('type')->get();
        $inventoryTypes = CodeDetail::where('CodeID', 'InventoryTypeStatus')
            ->whereNotIn('ID', InventoryType::whereNull('DeletedOn')->pluck('Type'))
            ->get();

        return view('inventory.itemmaster.inventorytype.create', compact('inventoryTypes'));
    }

    public function store(InventoryTypeRequest $request)
    {
        $this->authorize('create', InventoryType::class);

        $this->service->create($request->validated());

        return redirect()->route('inventorytype.index')->with('success', 'Inventory type created successfully.');
    }

    public function edit($id)
    {
        $type = InventoryType::with('type')->findOrFail($id);
        $inventoryTypes = CodeDetail::where('CodeID', 'InventoryTypeStatus')
            ->whereNotIn('ID', InventoryType::whereNull('DeletedOn')->pluck('Type'))
            ->get();
        $this->authorize('update', $type);

        return view('inventory.itemmaster.inventorytype.edit', compact('type', 'inventoryTypes'));
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
