<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\OpeningStockRequest;
use App\Models\Core\Branch;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\Store;
use App\Services\Inventory\OpenStockService;
use Illuminate\Http\Request;

class OpeningStockController extends Controller
{
    //
    public function index()
    {
        return view('inventory.stockmanagement.openingstockload.index');
    }

    public function create(){
        $branches = Branch::all();
        $stores = Store::all();
        $items  = ItemMasterList::all();
        return view('inventory.stockmanagement.openingstockload.create',compact('items','stores','branches'));
    }

    public function store(OpeningStockRequest $request)
    {
        $openstock = OpenStockService::create(
            $request->BranchId,
            $request->StoreId,
            $request->ItemCode,
            $request->Date,
            $request->Quantity,
            $request->UOM,
            $request->Value,
            $request->Remarks,
        );

            return redirect()->route('openingstock.index')
            ->with('success', 'Open stock created successfully');
    }
}
