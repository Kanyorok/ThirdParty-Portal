<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Exports\PriceManagementExport;
use App\Http\Requests\Inventory\PriceManagementRequest;
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

        $items = ItemMasterList::with('uom')
            ->whereNotIn('Id', function ($query) {
                $query->select('ItemID')->from('t_Pricing');
            })
            ->get();

        return view('inventory.pricemanagement.index', compact('prices', 'items'));
    }

    public function create()
    {
        $this->authorize('create', PriceManagement::class);
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.pricemanagement.create', compact('items', 'uoms'));
    }

    public function store(PriceManagementRequest $request)
    {
        $this->authorize('create', PriceManagement::class);
        $this->priceService->create($request->validated());

        return redirect()->route('pricemanagement.index')
            ->with('success', 'Price created and assigned to item!');
    }

    public function edit($id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->authorize('update', PriceManagement::class);
        $items = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.pricemanagement.edit', compact('price', 'items', 'uoms'));
    }

    public function update(PriceManagementRequest $request, $id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->authorize('update', PriceManagement::class);

        // Pass only validated data — FormRequest already skips ItemID/UOM for update
        $this->priceService->update($price, $request->validated());

        return redirect()->route('pricemanagement.index')
            ->with('success', 'Price updated!');
    }

    public function destroy($id)
    {
        $price = PriceManagement::findOrFail($id);
        $this->authorize('destroy', PriceManagement::class);
        $this->priceService->delete($price);

        return redirect()->route('pricemanagement.index')
            ->with('success', 'Price deleted.');
    }

    public function downloadSampleTemplate()
    {
        return Excel::download(new PriceManagementExport, 'price_management_sample.xlsx');
    }

    public function importPricing(PriceManagementRequest $request)
    {
        $this->authorize('update', PriceManagement::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv'
        ]);

        Excel::import(new PriceManagementImport, $request->file('file'));

        return back()->with('success', 'Pricing data imported successfully!');
    }
}
