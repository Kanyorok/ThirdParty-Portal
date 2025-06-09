<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Services\Inventory\PriceManagementService;
use App\Http\Requests\Inventory\PriceManagementRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PriceManagementController extends Controller
{
    protected $service;

    public function __construct(PriceManagementService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $prices = $this->service->list();
        return view('inventory.pricemanagement.index', compact('prices'));
    }

    public function create()
    {
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.pricemanagement.create', compact('items', 'uoms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'PriceID' => 'required|string|max:255',
            'ItemID' => 'required|exists:t_Items,Id',
            'UOM' => 'required|exists:t_UOM,Id', 
            'EstimatedPrice' => 'required|numeric|min:0',
            'ActualPrice' => 'required|numeric|min:0',
            'CurrencyCode' => 'required|string|max:10',
            'EffectiveFrom' => 'required|date',
            'EffectiveTo' => 'nullable|date|after_or_equal:EffectiveFrom',
            'IsDefault' => 'boolean',
            'Source' => 'nullable|string|max:255',
        ]);

        $validated['CreatedBy'] = auth()->id();
        $validated['CreatedOn'] = now();
        $validated['ModifiedBy'] = auth()->id();
        $validated['ModifiedOn'] = now();

        $this->service->create($validated);
        return redirect()->route('pricemanagement.index')->with('success', 'Price added successfully.');
    }

    public function edit($id)
    {
        $price = PriceManagement::findOrFail($id);
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.pricemanagement.edit', compact('price', 'items', 'uoms'));
    }

    public function update(Request $request, $id)
    {
        $price = PriceManagement::findOrFail($id);

        $validated = $request->validate([
            'PriceID' => 'required|string|max:255',
            'ItemID' => 'required|exists:t_Items,Id',
            'UOM' => 'required|exists:t_Items,Id', // Adjust if UOM is from a different table
            'EstimatedPrice' => 'required|numeric|min:0',
            'ActualPrice' => 'required|numeric|min:0',
            'CurrencyCode' => 'required|string|max:10',
            'EffectiveFrom' => 'required|date',
            'EffectiveTo' => 'nullable|date|after_or_equal:EffectiveFrom',
            'IsDefault' => 'boolean',
            'Source' => 'nullable|string|max:255',
        ]);

        $validated['ModifiedBy'] = auth()->id();
        $validated['ModifiedOn'] = now();

        $this->service->update($price, $validated);
        return redirect()->route('pricemanagement.index')->with('success', 'Price updated successfully.');
    }

    public function destroy($id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->service->delete($price);
        return redirect()->route('pricemanagement.index')->with('success', 'Price deleted.');
    }
}