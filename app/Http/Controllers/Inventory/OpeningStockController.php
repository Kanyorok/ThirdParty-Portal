<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\OpeningStockSampleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\OpeningStockRequest;
use App\Imports\OpeningStockImport;
use App\Models\Core\Branch;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\LoadOpeningStock;
use App\Models\Inventory\Store;
use App\Models\Inventory\UnitOfMeasure;
use App\Services\Inventory\OpenStockService;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class OpeningStockController extends Controller
{
    public function index()
    {
        $entries = LoadOpeningStock::with('item','branch','store','uom')->get();
        return view('inventory.stockmanagement.openingstockload.index', compact('entries'));
    }

    public function create(){

        $this->authorize('create', LoadOpeningStock::class);

        $branches = Branch::with('store')->get();
        $items  = ItemMasterList::all();
        $uoms = UnitOfMeasure::all();
        return view('inventory.stockmanagement.openingstockload.create', compact('items','branches','uoms'));
    }


    public function getstore($storeId)
    {
        $this->authorize('create', LoadOpeningStock::class);
        
        $stores = Store::where('BranchID',$storeId)->get();
        return response()->json($stores);
    }


    public function store(OpeningStockRequest $request)
    {
        $this->authorize('create', LoadOpeningStock::class);

        $validated = $request->validated();
        
        $openstock = OpenStockService::create(
            (int) $validated['BranchId'],
            (int) $validated['StoreId'],
            $validated['ItemCode'],
            Carbon::createFromFormat('d/m/Y', $validated['Date']),
            (int) $validated['Quantity'],
            (int) $validated['UOM'],
            (float) $validated['Value'],
            $validated['Remarks'] ?? null,
            auth()->user()

        );

            return redirect()->route('openingstock.index')
            ->with('success', 'Open stock created successfully');
    }

    public function downloadSampleTemplate()
    {
        return Excel::download(new OpeningStockSampleExport, 'opening_stock_sample.xlsx');
    }

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        Excel::import(new OpeningStockImport, $request->file('excel_file'));

        return redirect()->route('openingstock.index')->with('success', 'Bulk upload successful!');
    }

}
