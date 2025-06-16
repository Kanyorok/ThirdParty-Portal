<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockItem;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\Request;
use App\Enums\Inventory\Transfers;

class TransactionAdjustmentController extends Controller
{
    protected $service;

    public function __construct(StockAdjustmentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $adjustments = StockAdjustment::with('items')->latest('CreatedOn')->paginate(20);
        return view('inventory.transactions.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $branches = \App\Models\Core\Branch::all();
        return view('inventory.transactions.adjustments.create', compact('branches'));
    }
    

    public function store(StockAdjustmentRequest $request)
    {
    $this->service->create($request->validated(), $request->user());

    return redirect()->route('transactionsadjustment.index')->with('success', 'Stock adjustment recorded.');
    }


    public function getBranchStock($branchId)
    {
        $stockItems = StockItem::with('item')
            ->where('Branch', $branchId)
            ->get();

        return response()->json($stockItems);
    }

    public function approve(StockAdjustment $adjustment)
{
    $this->service->approve($adjustment->Id);
    return redirect()->back()->with('success', 'Stock adjustment approved.');
}

public function reject(StockAdjustment $adjustment)
{
    $this->service->reject($adjustment->Id);
    return redirect()->back()->with('success', 'Stock adjustment rejected.');
}

}
