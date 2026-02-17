<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryTypeRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryType;
use App\Services\Inventory\InventoryTypeService;
use Illuminate\Http\Request;

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

    /**
     * Check if inventory type has related items (active or inactive)
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkRelatedItems($id, Request $request)
    {
        $type = InventoryType::findOrFail($id);
        $this->authorize('update', $type);

        $checkType = $request->query('check_type', 'active');

        $result = $this->service->checkRelatedItems($type, $checkType);

        return response()->json($result);
    }

    public function update(InventoryTypeRequest $request, $id)
    {
        $type = InventoryType::findOrFail($id);
        $this->authorize('update', $type);

        $disableRelatedItems = $request->input('disable_related_items', false) == '1';
        $enableRelatedItems = $request->input('enable_related_items', false) == '1';
        $wasActive = $type->Status == 1;
        $wasInactive = $type->Status == 0;
        $willBeActive = $request->input('Status') == 1;
        $willBeInactive = $request->input('Status') == 0;

        try {
            $this->service->update($type, $request->validated(), $disableRelatedItems, $enableRelatedItems);
            if ($wasActive && $willBeInactive && $disableRelatedItems) {
                return redirect()
                    ->route('inventorytype.index')
                    ->with('success', "Inventory type deactivated successfully. Related item(s) were also deactivated.");
            } elseif ($wasInactive && $willBeActive && $enableRelatedItems) {
                return redirect()
                    ->route('inventorytype.index')
                    ->with('success', "Inventory type activated successfully. Related item(s) were also activated.");
            } else {
                return redirect()
                    ->route('inventorytype.index')
                    ->with('success', 'Inventory type updated successfully.');
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('inventorytype.index')
                ->with('error', 'An error occurred while updating the inventory type. Please try again.');
        }
    }

    public function destroy($id)
    {
        $type = InventoryType::findOrFail($id);
        $this->authorize('destroy', $type);

        $this->service->delete($type);

        return redirect()->route('inventorytype.index')->with('success', 'Inventory type deleted successfully.');
    }
}
