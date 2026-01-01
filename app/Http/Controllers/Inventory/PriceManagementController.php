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
use App\Models\Core\Currency;
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
            ->whereNotIn('Id', PriceManagement::with('currency')->select('ItemID'))
            ->withTrashed()
            ->get();

        $currencies = Currency::all();    
        return view('inventory.pricemanagement.index', compact('prices', 'items', 'currencies'));
    }

    public function create()
    {
        $this->authorize('create', PriceManagement::class);
        $items = ItemMasterList::all();
        $currencies = Currency::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.pricemanagement.create', compact('items', 'currencies', 'uoms'));
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
        $currencies = Currency::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.pricemanagement.edit', compact('price', 'items', 'currencies', 'uoms'));
    }

    public function update(PriceManagementRequest $request, $id)
    {
        $oldPrice = PriceManagement::findOrFail($id);
        $this->authorize('update', PriceManagement::class);

        $oldPrice->update([
            'DeletedOn' => now(),
            'DeletedBy' => auth()->id(),
        ]);

        $data = $request->validated();
        $data['PriceID'] = $oldPrice->PriceID;
        $data['ItemID'] = $oldPrice->ItemID;
        $data['UOM'] = $oldPrice->UOM;

        $this->priceService->create($data);

        return redirect()->route('pricemanagement.index')
            ->with('success', 'Price updated (new version created)!');
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
