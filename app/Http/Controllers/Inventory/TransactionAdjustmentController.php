<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Branch;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\StockItem;
use App\Services\Inventory\StockAdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionAdjustmentController extends Controller
{
    protected $service;

    public function __construct(StockAdjustmentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', StockAdjustment::class);

        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
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
        $branchId = $currentBranch->Id;

        $users = User::whereHas('employee', function ($q) use ($branchId) {
            $q->where('BranchId', $branchId);
        })->get();

        $reasons = CodeDetail::where('CodeID', 'AdjustmentReason')->orderBy('Description', 'asc')->get();

        $stockItems = StockItem::with(['item', 'uom'])
            ->where('Branch', $branchId)
            ->get();

        $currentUser = $request->user();

        return view('inventory.transactions.adjustments.create', compact(
            'currentBranch',
            'users',
            'reasons',
            'stockItems',
            'currentUser'
        ));
    }

    public function store(StockAdjustmentRequest $request)
    {
        $this->authorize('create', StockAdjustment::class);

        try {
            $this->service->create($request->validated());

            return redirect()->route('transactionsadjustment.index')
                ->with('success', 'Stock adjustment recorded successfully.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Failed to create stock adjustment: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    public function getBranchStock($branchId)
    {
        $stockItems = StockItem::with('item', 'uom')
            ->where('Branch', $branchId)
            ->get();

        return response()->json($stockItems);
    }

    public function approve(StockAdjustment $stockAdjustment, Request $request)
    {
        $this->authorize('approve', $stockAdjustment);

        $comments = $request->input('comments');
        $this->service->approve($stockAdjustment->Id, $comments);

        return redirect()->back()->with('success', 'Stock adjustment approved.');
    }

    public function edit(StockAdjustment $stockAdjustment, Request $request)
    {
        $this->authorize('update', $stockAdjustment);

        $currentBranch = $request->user()->branch;
        $branchId = $currentBranch->Id;

        if ($stockAdjustment->Branch != $branchId) {
            return redirect()->route('transactionsadjustment.index')
                ->with('error', 'You can only edit adjustments from your own branch.');
        }

        $adjustment = $stockAdjustment->load(['items.item', 'branch']);

        $itemIdsInAdjustment = $adjustment->items->pluck('Item')->toArray();

        $currentStocksInBranch = StockItem::where('Branch', $adjustment->Branch)
            ->whereIn('ItemID', $itemIdsInAdjustment)
            ->pluck('CurrentQty', 'ItemID');

        $adjustment->items->each(function ($adjItem) use ($currentStocksInBranch) {
            $adjItem->current_stock_qty = $currentStocksInBranch->get($adjItem->Item, 0);
        });

        $users = User::whereHas('employee', function ($q) use ($branchId) {
            $q->where('BranchId', $branchId);
        })->get();

        $reasons = CodeDetail::where('CodeID', 'AdjustmentReason')
            ->orderBy('Description', 'asc')
            ->get();    

        return view('inventory.transactions.adjustments.edit', compact(
            'adjustment',
            'currentBranch',
            'users',
            'reasons'
        ));
    }

    public function update(StockAdjustmentRequest $request, StockAdjustment $stockAdjustment)
    {
        $currentBranch = $request->user()->branch;
        $branchId = $currentBranch->Id;

        if ($stockAdjustment->Branch != $branchId) {
            return redirect()->route('transactionsadjustment.index')
                ->with('error', 'You can only update adjustments from your own branch.');
        }

        $validated = $request->validated();
        $this->service->update($stockAdjustment, $validated);

        return redirect()->route('transactionsadjustment.index')
            ->with('success', 'Stock adjustment updated successfully.');
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

        $reasons = CodeDetail::where('CodeID', 'AdjustmentReason')->get();

        return view('inventory.transactions.adjustments.show', compact(
            'adjustment',
            'reasons'
        ));
    }

    public function destroy(StockAdjustment $stockAdjustment, Request $request)
    {
        $this->authorize('destroy', $stockAdjustment);

        $currentBranch = $request->user()->branch;
        $branchId = $currentBranch->Id;

        if ($stockAdjustment->Branch != $branchId) {
            return redirect()->route('transactionsadjustment.index')
                ->with('error', 'You can only delete adjustments from your own branch.');
        }

        $stockAdjustment->items()->delete();
        $stockAdjustment->delete();

        return redirect()->route('transactionsadjustment.index')
            ->with('success', 'Stock adjustment deleted successfully.');
    }

    public function reject(StockAdjustment $stockAdjustment, Request $request)
    {
        $this->authorize('approve', $stockAdjustment);

        $comments = $request->input('comments');
        $this->service->reject($stockAdjustment->Id, $comments);

        return redirect()->back()->with('success', 'Stock adjustment rejected.');
    }
}
