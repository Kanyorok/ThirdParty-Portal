<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\ItemTypeRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\ItemType;
use App\Services\Inventory\ItemTypeService;

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

        return redirect()->route('itemtype.index')->with('success', 'Inventory type created successfully.');
    }

    public function show($Id)
    {
        $itemtypes = ItemType::with('type')->get();
        $itmTypes = CodeDetail::where('CodeID', 'ItemTypeStatus')->get();
        $this->authorize('view', $itemtypes);

        return response()->json($itemtypes);
    }

    public function edit($Id)
    {
        $itemtype = ItemType::findOrFail($Id);
        $itmTypes = CodeDetail::where('CodeID', 'ItemTypeStatus')->whereNotIn('ID', ItemType::whereNull('DeletedOn')->pluck('TypeName'))
            ->get();
        $this->authorize('update', $itemtype);

        return response()->json($itemtype);
    }

    public function update(ItemTypeRequest $request, $Id)
    {
        $itemtype = ItemType::findOrFail($Id);
        $this->authorize('update', $itemtype);

        $this->service->update($itemtype, $request->validated());

        return redirect()->route('itemtype.index')->with('success', 'Item Type updated successfully.');
    }

    public function destroy($Id)
    {
        $itemtype = ItemType::findOrFail($Id);
        $this->authorize('destroy', $itemtype);

        $this->service->delete($itemtype);

        return redirect()->route('itemtype.index')->with('success', 'Item Type deleted successfully.');
    }
}
