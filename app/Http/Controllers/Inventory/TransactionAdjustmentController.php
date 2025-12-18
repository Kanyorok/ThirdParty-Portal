<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockItem;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Log;

class TransactionAdjustmentController extends Controller
{
    protected $service;

    public function __construct(StockAdjustmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request) 
    {
        $currentBranch = $request->user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $adjustments = StockAdjustment::with('items')
            ->latest('CreatedOn')
            ->where('Branch', $branchId)
            ->paginate(20);
        
        return view('inventory.transactions.adjustments.index', compact('adjustments'));
    }

   public function create(Request $request)
{
    $this->authorize('create', StockAdjustment::class);
    
    $currentBranch = $request->user()->branch;
    if (!$currentBranch instanceof Branch) {
        return redirect()->back()->with('fail', 'Current user branch not found.');
    }

    $branchId = $currentBranch->Id;
    $branch = Branch::findOrFail($branchId);

    $users = User::whereHas('employee', function ($q) use ($branchId) {
            $q->where('BranchId', $branchId);
        })
        ->get();

    $reasons = CodeDetail::where('CodeID', 'AdjustmentReason')->get();
    $stockItems = StockItem::with(['item', 'uom'])
        ->where('Branch', $branchId)
        ->get();

    $currentUser = $request->user();
    
    return view('inventory.transactions.adjustments.create', compact('branch', 'users', 'reasons', 'stockItems', 'currentUser'));
}
    public function store(StockAdjustmentRequest $request)
    {
        $this->authorize('create', StockAdjustment::class);
        $this->service->create($request->validated());

        return redirect()->route('transactionsadjustment.index')->with('success', 'Stock adjustment recorded.');
    }

    public function getBranchStock($branchId)
    {
        $stockItems = StockItem::with('item', 'uom')
            ->where('Branch', $branchId)
            ->get();

        return response()->json($stockItems);
    }

    public function approve(StockAdjustment $stockAdjustment, Request $request)  // Add Request for comments
    {
        $comments = $request->input('comments');
        $this->service->approve($stockAdjustment->Id, $comments);
        
        return redirect()->back()->with('success', 'Stock adjustment approved.');
    }

    public function edit(StockAdjustment $stockAdjustment)
    {
        $this->authorize('update', $stockAdjustment);

        $adjustment = $stockAdjustment->load(['items.item', 'branch']);

        $itemIdsInAdjustment = $adjustment->items->pluck('Item')->toArray();

        $currentStocksInBranch = StockItem::where('Branch', $adjustment->Branch)
            ->whereIn('ItemID', $itemIdsInAdjustment)
            ->pluck('CurrentQty', 'ItemID');
        
        $adjustment->items->each(function ($adjItem) use ($currentStocksInBranch) {
            $adjItem->current_stock_qty = $currentStocksInBranch->get($adjItem->Item, 0);
        });

        $branches = Branch::all();
        $users = User::all();
        $reasons = CodeDetail::where('CodeID', 'AdjustmentReason')->get();
        
        return view('inventory.transactions.adjustments.edit', compact('adjustment', 'branches', 'users', 'reasons'));
    }

    public function update(StockAdjustmentRequest $request, StockAdjustment $stockAdjustment)
    {
        Log::info('TransactionAdjustmentController@update: Attempting to update StockAdjustment ID: ' . $stockAdjustment->Id);
        $validated = $request->validated();
        $this->service->update($stockAdjustment, $validated);
        
        return redirect()->route('transactionsadjustment.index')->with('success', 'Stock adjustment updated successfully.');
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $this->authorize('view', $stockAdjustment);

        $adjustment = $stockAdjustment->load(['branch', 'items.item', 'adjustedBy']);

        $itemIdsInAdjustment = $adjustment->items->pluck('Item')->toArray();

        $currentStocksInBranch = StockItem::where('Branch', $adjustment->Branch)
            ->whereIn('ItemID', $itemIdsInAdjustment)
            ->pluck('CurrentQty', 'ItemID');

        $adjustment->items->each(function ($adjItem) use ($currentStocksInBranch) {
            $adjItem->current_stock_qty = $currentStocksInBranch->get($adjItem->Item, 0);
        });

        return view('inventory.transactions.adjustments.show', compact('adjustment'));
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        $this->authorize('destroy', $stockAdjustment);
        $stockAdjustment->items()->delete();
        $stockAdjustment->delete();

        return redirect()->route('transactionsadjustment.index')->with('success', 'Stock adjustment deleted.');
    }

    public function reject(StockAdjustment $stockAdjustment, Request $request)  // Add Request for comments
    {
        $comments = $request->input('comments');
        $this->service->reject($stockAdjustment->Id, $comments);
        
        return redirect()->back()->with('success', 'Stock adjustment rejected.');
    }
}