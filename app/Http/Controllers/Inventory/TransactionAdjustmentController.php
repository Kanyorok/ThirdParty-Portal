<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockAdjustmentItem;
use App\Models\Inventory\StockItem;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\Request;
use App\Enums\Inventory\Transfers;
use App\Models\Core\Branch;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Log; 

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
        $this->authorize('create', StockAdjustment::class);
        $branches = \App\Models\Core\Branch::all();
        $users = User::all();
        return view('inventory.transactions.adjustments.create', compact('branches','users'));
    }

    public function store(StockAdjustmentRequest $request)
    {
        $this->authorize('create', StockAdjustment::class);
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

    public function approve(StockAdjustment $stockAdjustment) 
    {

        $this->service->approve($stockAdjustment->Id);
        return redirect()->back()->with('success', 'Stock adjustment approved.');
    }

    public function edit(StockAdjustment $stockAdjustment) 
    {
        $this->authorize('create', StockAdjustment::class); 
        $adjustment = $stockAdjustment->load(['items.item', 'items.stockItem','branch']); 
        $stockItems = StockItem::with('item')->where('Branch', $adjustment->Branch)->get();
        $branches = \App\Models\Core\Branch::all();
        $users = User::all();
        return view('inventory.transactions.adjustments.edit', compact('adjustment', 'stockItems', 'branches', 'users'));
    }

    public function update(StockAdjustmentRequest $request, StockAdjustment $stockAdjustment) 
    {
        \Log::info('TransactionAdjustmentController@update: Attempting to update StockAdjustment ID: ' . $stockAdjustment->Id);
        $validated = $request->validated();
        $this->service->update($stockAdjustment, $validated); 
        return redirect()->route('transactionsadjustment.index')->with('success', 'Stock adjustment updated successfully.');
    }

    public function show(StockAdjustment $stockAdjustment) 
    {
        $this->authorize('view', StockAdjustment::class);
        $adjustment = $stockAdjustment->load(['branch','items.item', 'adjustedBy']); 
        return view('inventory.transactions.adjustments.show', compact('adjustment'));
    }

    public function destroy(StockAdjustment $stockAdjustment) 
    {
        $this->authorize('destroy', $stockAdjustment);
        $stockAdjustment->items()->delete(); 
        $stockAdjustment->delete();         

        return redirect()->route('transactionsadjustment.index')->with('success', 'Stock adjustment deleted.');
    }

    public function reject(StockAdjustment $stockAdjustment) 
    {
        $this->service->reject($stockAdjustment->Id);
        return redirect()->back()->with('success', 'Stock adjustment rejected.');
    }
}