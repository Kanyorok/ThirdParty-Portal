<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Inventory\PriceManagementService;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Imports\PriceManagementImport;
use Maatwebsite\Excel\Facades\Excel;

class PriceManagementController extends Controller
{
    protected $priceService;

    public function __construct(PriceManagementService $priceService)
    {
        $this->priceService = $priceService;
    }

    public function index()
    {
        $prices = $this->priceService->list();
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();

        return view('inventory.pricemanagement.index', compact('prices', 'items', 'uoms'));
    }

    public function create()
    {
        $this->authorize('create', PriceManagement::class);
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.pricemanagement.create', compact('items', 'uoms'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', PriceManagement::class);
        $this->priceService->create($request->all());

        return redirect()->route('pricemanagement.index')->with('success', 'Price created and assigned to item!');
    }

    public function edit($id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->authorize('update', PriceManagement::class);
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.pricemanagement.edit', compact('price', 'items', 'uoms'));
    }

    public function update(Request $request, $id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->authorize('update', PriceManagement::class);
        $this->priceService->update($price, $request->all());

        return redirect()->route('pricemanagement.index')->with('success', 'Price updated!');
    }

    public function destroy($id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->authorize('destroy', PriceManagement::class);
        $this->priceService->delete($price);

        return redirect()->route('pricemanagement.index')->with('success', 'Price deleted.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new PriceManagementImport, $request->file('file'));

        return redirect()->route('pricemanagement.index')->with('success', 'Price Management data imported successfully.');
    }


}