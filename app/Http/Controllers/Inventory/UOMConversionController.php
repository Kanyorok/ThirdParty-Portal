<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

use App\Models\Inventory\UOMConversion;
use App\Models\Inventory\UnitOfMeasure;
use App\Http\Requests\Inventory\UOMConversionRequest;
use App\Http\Controllers\Inventory\ItemMasterListController;
use App\Services\Inventory\UOMConversionService;
use App\Policies\Inventory\UOMConversionPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Inventory\ItemMasterList;
use App\Models\Core\User;


use Illuminate\Http\Request;

class UOMConversionController extends Controller
{
    protected UOMConversionService $service;

    public function __construct(UOMConversionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', UOMConversion::class);
        $uomConversions = UOMConversion::all();
        $items = ItemMasterList::with('uom')->get();
        return view('inventory.uomconversion.index', compact('uomConversions', 'items'));
    }

    public function create()
    {
        $this->authorize('create', UOMConversion::class);
        $uoms = UnitOfMeasure::all();
        $alternateUoms = UnitOfMeasure::where('BaseUnit', '0')->get();
        $items = ItemMasterList::with('uom')->get();

        return view('inventory.uomconversion.create', compact('uoms', 'alternateUoms', 'items'));
    }

    public function store(UOMConversionRequest $request)
    {
        $this->authorize('create', UOMConversion::class);
        $validated = $request->validated();
        $uomConversion = $this->service->create($validated);

        return redirect()
            ->route("uomconversion.index")
            ->with('success', 'UOM Conversion created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('update', UOMConversion::class);
        $uomConversion = UOMConversion::findOrFail($id);
        $uoms = UnitOfMeasure::all();
        $alternateUoms = UnitOfMeasure::where('BaseUnit', '0')->get();
        $items = ItemMasterList::with('uom')->get();

        return view('inventory.uomconversion.edit', compact('items', 'uomConversion', 'uoms', 'alternateUoms'));
    }

    public function update(UOMConversionRequest $request, $id)
    {
        $this->authorize('update', UOMConversion::class);
        $uomConversion = UOMConversion::findOrFail($id);
        $this->service->update($uomConversion, $request->validated());
        return redirect()
            ->route('uomconversion.index')
            ->with('success', 'UOM Conversion updated successfully.');
    }

    public function show($id)
    {
        $this->authorize('view', UOMConversion::class);
        $uomConversion = UOMConversion::findOrFail($id);
        return view('inventory.uomconversion.show', compact('uomConversion'));
    }

    public function destroy(string $id)
    {
        $this->authorize('destroy', UOMConversion::class);
        $uomConversion = UOMConversion::findOrFail($id);

        $this->service->delete($uomConversion);

        return redirect()->route('uomconversion.index')->with('success', 'UOM Conversion deleted successfully.');
    }


}
