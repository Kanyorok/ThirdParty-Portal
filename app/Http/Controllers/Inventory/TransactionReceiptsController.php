<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\Transfers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransactionReceiptRequest;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\Store;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\TransactionTransfer;
use App\Services\Inventory\TransactionReceiptService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CodeDetail;

class TransactionReceiptsController extends Controller
{
    protected TransactionReceiptService $service;

    public function __construct(TransactionReceiptService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('view', TransactionReceipt::class);

        // Get current user's branch
        $currentBranch = Auth::user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        // Only show receipts for transfers to current user's branch
        $receipts = TransactionReceipt::with([
            'transfer', 'transfer.fromBranch', 'items.transferItem', 'items.item'
        ])
            ->whereHas('transfer', function ($query) use ($branchId) {
                $query->where('ToBranch', $branchId);
            })
            ->latest()
            ->paginate(10);

        return view('inventory.transactions.receipts.index', compact('receipts'));
    }

   public function create()
{
    $this->authorize('create', TransactionReceipt::class);
    $currentBranch = Auth::user()->branch;
    if (!$currentBranch instanceof Branch) {
        \Log::warning('TransactionReceiptsController::create - user has no branch', ['user_id' => Auth::id()]);
        return redirect()->back()->with('fail', 'Current user branch not found.');
    }

    $branchId = $currentBranch->Id;
    $inTransitValue = Transfers::InTransit->value;

    $transfers = TransactionTransfer::doesntHave('receipt')
        ->with(['items.item', 'fromBranch'])
        ->where('ToBranch', $branchId)
        ->where('Status', $inTransitValue)
        ->get();
    
    // Get current user object - already done in your code
    $currentUser = Auth::user();

    return view('inventory.transactions.receipts.create', compact(
        'transfers', 
        'currentUser'  // Pass the user object
    ));
}
    public function store(TransactionReceiptRequest $request)
    {
        $this->authorize('create', TransactionReceipt::class);

        // Get current user's branch
        $currentBranch = Auth::user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $validatedData = $request->validated();
        $items = $validatedData['items'] ?? [];
        unset($validatedData['items']);

        $transfer = TransactionTransfer::findOrFail($validatedData['TransferID']);

        // Verify the transfer belongs to current user's branch
        if ($transfer->ToBranch != $branchId) {
            abort(403, 'You can only receive transfers destined for your branch.');
        }

        // Verify transfer is still in transit
        if ($transfer->Status != Transfers::InTransit->value) {
            abort(403, 'Only transfers in transit can be received.');
        }

        // Verify transfer doesn't already have a receipt
        if ($transfer->receipt()->exists()) {
            abort(403, 'This transfer has already been received.');
        }

        try {
            $receipt = $this->service->createReceipt($validatedData, $items);
            return redirect()
                ->route('transactionsreceipts.index')
                ->with('success', 'Transaction receipt posted successfully.');
        } catch (ValidationException $e) {
            $itemsWithDetails = collect($items)->map(function ($item) use ($branchId) {
                $itemModel = ItemMasterList::find($item['item']);
                $item['item_name'] = $itemModel->ItemName ?? 'Unknown';

                $storeOptions = Store::where('BranchID', $branchId)
                    ->get(['Id', 'StoreName'])
                    ->map(fn($s) => ['Id' => $s->Id, 'StoreName' => $s->StoreName])
                    ->toArray();

                $item['store_options'] = $storeOptions;
                return $item;
            })->toArray();

            return redirect()
                ->back()
                ->withInput(array_merge($validatedData, ['items' => $itemsWithDetails]))
                ->withErrors($e->validator);
        }
    }

    public function show($id)
    {
        $this->authorize('view', TransactionReceipt::class);

        // Get current user's branch
        $currentBranch = Auth::user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $receipt = TransactionReceipt::with(['transfer', 'items.item', 'receivedBy'])
            ->findOrFail($id);

        // Verify the receipt's transfer belongs to current user's branch
        if ($receipt->transfer->ToBranch != $branchId) {
            abort(403, 'You can only view receipts for transfers destined for your branch.');
        }

        return view('inventory.transactions.receipts.show', compact('receipt'));
    }

    public function destroy($id)
    {
        $this->authorize('destroy', TransactionReceipt::class);

        // Get current user's branch
        $currentBranch = Auth::user()->branch;
        if (!$currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $receipt = TransactionReceipt::with('transfer')->findOrFail($id);

        // Verify the receipt's transfer belongs to current user's branch
        if ($receipt->transfer->ToBranch != $branchId) {
            abort(403, 'You can only delete receipts for transfers destined for your branch.');
        }

        $this->service->delete($receipt);

        return redirect()->route('transactionsreceipts.index')->with('success', 'Receipt deleted.');
    }

    public function getTransferItems($id)
    {
        // Get current user's branch
        $currentBranch = Auth::user()->branch;
        if (!$currentBranch instanceof Branch) {
            return response()->json(['error' => 'Current user branch not found.'], 403);
        }

        $branchId = $currentBranch->Id;

        $transfer = TransactionTransfer::with([
            'items.item.price',
            'items.item.uom',
            'ToBranch'
        ])->findOrFail($id);

        // Verify the transfer belongs to current user's branch
        if ($transfer->ToBranch != $branchId) {
            return response()->json(['error' => 'You can only access transfers destined for your branch.'], 403);
        }

        // Verify transfer is in transit
        if ($transfer->Status != Transfers::InTransit->value) {
            return response()->json(['error' => 'Only transfers in transit can be received.'], 403);
        }

        // Verify transfer doesn't already have a receipt
        if ($transfer->receipt()->exists()) {
            return response()->json(['error' => 'This transfer has already been received.'], 403);
        }

        $branchStores = Store::where('BranchID', $branchId)
            ->select('Id', 'StoreName')
            ->get();

        $itemsWithStores = $transfer->items->map(function ($transferItem) use ($branchStores) {
            $item = $transferItem->item;

            return [
                'Item' => $item->Id,
                'DispatchedQty' => $transferItem->DispatchedQty,
                'item' => [
                    'ItemName' => $item->ItemName,
                    'Id' => $item->Id,
                    'uom' => [
                        'Code' => $item->uom?->Code ?? 'N/A'
                    ],
                ],
                'stores' => $branchStores,
                'UnitCost' => $item->price?->ActualPrice ?? 0,
                'UOM' => $item->UOM,
                'UOMCode' => $item->uom?->Code ?? 'N/A',
                'PriceID' => $item->price?->Id ?? null,
            ];
        });

        return response()->json([
            'items' => $itemsWithStores,
            'from_branch' => $branchId,
        ]);
    }
}