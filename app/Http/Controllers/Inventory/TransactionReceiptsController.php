<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\Transfers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransactionReceiptRequest;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Inventory\StockGRNLedger;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\Store;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\TransactionTransfer;
use App\Services\Inventory\TransactionReceiptService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

        $currentBranch = Auth::user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $receipts = TransactionReceipt::with([
            'transfer', 'transfer.fromBranch', 'items.transferItem', 'items.item',
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
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $inTransitValue = Transfers::InTransit->value;

        $transfers = TransactionTransfer::doesntHave('receipt')
            ->with(['items.item', 'fromBranch'])
            ->where('ToBranch', $branchId)
            ->where('Status', $inTransitValue)
            ->get();

        $currentUser = Auth::user();

        return view('inventory.transactions.receipts.create', compact(
            'transfers',
            'currentUser'
        ));
    }

    public function store(TransactionReceiptRequest $request)
    {
        $this->authorize('create', TransactionReceipt::class);

        $currentBranch = Auth::user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $validatedData = $request->validated();
        $items = $validatedData['items'] ?? [];
        unset($validatedData['items']);

        $transfer = TransactionTransfer::findOrFail($validatedData['TransferID']);

        if ($transfer->ToBranch != $branchId) {
            abort(403, 'You can only receive transfers destined for your branch.');
        }

        if ($transfer->Status != Transfers::InTransit->value) {
            abort(403, 'Only transfers in transit can be received.');
        }

        if ($transfer->receipt()->exists()) {
            abort(403, 'This transfer has already been received.');
        }

        try {
            $receipt = $this->service->createReceipt($validatedData, $items);

            return redirect()
                ->route('transactionsreceipts.index')
                ->with('success', 'Transaction receipt posted successfully with FIFO costing.');
        } catch (ValidationException $e) {
            $itemsWithDetails = collect($items)->map(function ($item) use ($branchId) {
                $itemModel = ItemMasterList::find($item['item']);
                $item['item_name'] = $itemModel->ItemName ?? 'Unknown';

                $storeOptions = Store::where('BranchID', $branchId)
                    ->get(['Id', 'StoreName'])
                    ->map(fn ($s) => ['Id' => $s->Id, 'StoreName' => $s->StoreName])
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

        $currentBranch = Auth::user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $receipt = TransactionReceipt::with([
            'transfer',
            'items.item',
            'receivedBy',
            'transfer.items' => function ($query) {
                $query->with('item');
            },
        ])->findOrFail($id);

        if ($receipt->transfer->ToBranch != $branchId) {
            abort(403, 'You can only view receipts for transfers destined for your branch.');
        }

        $grnLedgerEntries = StockGRNLedger::where('SourceType', 'transfer')
            ->where('SourceReference', $receipt->transfer->TransferId)
            ->with(['goodsReceipt'])
            ->get()
            ->groupBy('ItemNo');

        return view('inventory.transactions.receipts.show', compact('receipt', 'grnLedgerEntries'));
    }

    public function destroy($id)
    {
        $this->authorize('destroy', TransactionReceipt::class);

        $currentBranch = Auth::user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;

        $receipt = TransactionReceipt::with('transfer')->findOrFail($id);

        if ($receipt->transfer->ToBranch != $branchId) {
            abort(403, 'You can only delete receipts for transfers destined for your branch.');
        }

        $this->service->delete($receipt);

        return redirect()->route('transactionsreceipts.index')->with('success', 'Receipt deleted.');
    }

    public function getTransferItems($id)
{
    $currentBranch = Auth::user()->branch;
    if (!$currentBranch instanceof Branch) {
        return response()->json(['error' => 'Current user branch not found.'], 403);
    }

        $branchId = $currentBranch->Id;

        $transfer = TransactionTransfer::with([
            'items.item.price',
            'items.item.uom',
            'ToBranch',
        ])->findOrFail($id);

    if ($transfer->ToBranch != $branchId) {
        return response()->json(['error' => 'You can only access transfers destined for your branch.'], 403);
    }

    if ($transfer->Status != Transfers::InTransit->value) {
        return response()->json(['error' => 'Only transfers in transit can be received.'], 403);
    }

    if ($transfer->receipt()->exists()) {
        return response()->json(['error' => 'This transfer has already been received.'], 403);
    }

    $mainStore = Store::where('BranchID', $branchId)
        ->where('IsMainStore', true)
        ->first();

        if (! $mainStore) {
            return response()->json(['error' => 'No main store found for your branch. Please contact admin.'], 404);
        }

        $itemsWithStores = $transfer->items->map(function ($transferItem) use ($mainStore) {
            $item = $transferItem->item;
            $batchAllocations = json_decode($transferItem->BatchAllocation, true) ?? [];

            return [
                'Item' => $item->Id,
                'DispatchedQty' => $transferItem->DispatchedQty,
                'item' => [
                    'ItemName' => $item->ItemName,
                    'Id' => $item->Id,
                    'uom' => [
                        'Code' => $item->uom?->Code ?? 'N/A',
                    ],
                ],
                'main_store' => [
                    'Id' => $mainStore->Id,
                    'StoreName' => $mainStore->StoreName,
                ],
                'UnitCost' => $transferItem->UnitCost ?? $item->price?->ActualPrice ?? 0,
                'UOM' => $item->UOM,
                'UOMCode' => $item->uom?->Code ?? 'N/A',
                'PriceID' => $item->price?->Id ?? null,
                'batch_allocation' => $batchAllocations,
                'allocation_type' => ! empty($batchAllocations) ? 'specific' : 'fifo',
            ];
        });

        return response()->json([
            'items' => $itemsWithStores,
            'from_branch' => $branchId,
            'transfer_id' => $transfer->TransferId,
            'main_store' => $mainStore,
        ]);
    }
}
