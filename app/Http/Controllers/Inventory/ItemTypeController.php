<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemType;
use App\Http\Requests\Inventory\ItemTypeRequest;
use App\Services\Inventory\ItemTypeService;
use Illuminate\Support\Facades\Auth;

class ItemTypeController extends Controller
{

    protected ItemTypeService $service;

    public function __construct(ItemTypeService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $itemtypes = ItemType::all();
        return view('inventory.itemmaster.itemtype.index', compact('itemtypes'));
    }

    public function create()
    {

        $this->authorize('create', ItemType::class);
        return view('inventory.itemmaster.itemtype.create');
    }

    public function store(ItemTypeRequest $request)
    {
        $this->authorize('create', ItemType::class);
        $this->service->create($request->validated());

        return redirect()->route('itemtype.index')->with('success', 'Inventory type created successfully.');
    }

    public function show($Id)
    {
        $itemtype = ItemType::findOrFail($Id);
        $this->authorize('view', $itemtype);
        return response()->json($itemtype);
    }

    public function edit($Id)
    {
        $itemtype = ItemType::findOrFail($Id);
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
