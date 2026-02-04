<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ItemTypeRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\ItemType;
use App\Services\Inventory\ItemTypeService;
use Illuminate\Http\Request;

class ItemTypeController extends Controller
{
    protected ItemTypeService $service;

    public function __construct(ItemTypeService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', ItemType::class);
        $itemTypes = ItemType::with('type')->get();
        $itmTypes = CodeDetail::where('CodeID', 'ItemTypeStatus')->get();

        return view('inventory.itemmaster.itemtype.index', compact('itemTypes', 'itmTypes'));
    }

    public function create()
    {
        $this->authorize('create', ItemType::class);
        $itemtypes = ItemType::with('type')->get();
        $itmTypes = CodeDetail::where('CodeID', 'ItemTypeStatus')
            ->whereNotIn('ID', ItemType::whereNull('DeletedOn')->pluck('TypeName'))
            ->get();

        return view('inventory.itemmaster.itemtype.create', compact('itmTypes'));
    }

    public function store(ItemTypeRequest $request)
    {
        $this->authorize('create', ItemType::class);

        $this->service->create($request->validated());

        return redirect()->route('itemtype.index')->with('success', 'Item type created successfully.');
    }

    public function show($Id)
    {
        $this->authorize('view', ItemType::class);
        $itemtypes = ItemType::with('type')->get();
        $itmTypes = CodeDetail::where('CodeID', 'ItemTypeStatus')->get();

        return response()->json($itemtypes);
    }

    public function edit($Id)
    {
        $this->authorize('update', ItemType::class);
        $itemtype = ItemType::findOrFail($Id);
        $itmTypes = CodeDetail::where('CodeID', 'ItemTypeStatus')
            ->whereNotIn('ID', ItemType::whereNull('DeletedOn')->pluck('TypeName'))
            ->get();

        return response()->json($itemtype);
    }

    /**
     * Check if item type has related items (active or inactive)
     *
     * @param int $Id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkRelatedItems($Id, Request $request)
    {
        $itemType = ItemType::findOrFail($Id);
        $this->authorize('update', ItemType::class);

        // Determine what type of check to perform (active or inactive items)
        $checkType = $request->query('check_type', 'active');

        $result = $this->service->checkRelatedItems($itemType, $checkType);

        return response()->json($result);
    }

    public function update(ItemTypeRequest $request, $Id)
    {
        $this->authorize('update', ItemType::class);
        $itemtype = ItemType::findOrFail($Id);

        $disableRelatedItems = $request->input('disable_related_items', false) == '1';
        $enableRelatedItems = $request->input('enable_related_items', false) == '1';

        // Check what action is being performed
        $wasActive = $itemtype->Active == 1;
        $wasInactive = $itemtype->Active == 0;
        $willBeActive = $request->input('Active') == 1;
        $willBeInactive = $request->input('Active') == 0;

        try {
            $this->service->update($itemtype, $request->validated(), $disableRelatedItems, $enableRelatedItems);

            // Provide specific success message based on action
            if ($wasActive && $willBeInactive && $disableRelatedItems) {
                return redirect()
                    ->route('itemtype.index')
                    ->with('success', "Item type deactivated successfully. Related item(s) were also deactivated.");
            } elseif ($wasInactive && $willBeActive && $enableRelatedItems) {
                return redirect()
                    ->route('itemtype.index')
                    ->with('success', "Item type activated successfully. Related item(s) were also activated.");
            } else {
                return redirect()
                    ->route('itemtype.index')
                    ->with('success', 'Item type updated successfully.');
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('itemtype.index')
                ->with('error', 'An error occurred while updating the item type. Please try again.');
        }
    }

    public function destroy($Id)
    {
        $this->authorize('delete', ItemType::class);
        $itemtype = ItemType::findOrFail($Id);

        $this->service->delete($itemtype);

        return redirect()->route('itemtype.index')->with('success', 'Item type deleted successfully.');
    }
}
