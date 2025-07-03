<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionReceipt;
use App\Http\Requests\Inventory\TransactionReceiptRequest;
use App\Services\Inventory\TransactionReceiptService;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Enums\Inventory\Transfers;

class TransactionReceiptsController extends Controller
{
    protected TransactionReceiptService $service;

    public function __construct(TransactionReceiptService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $receipts = TransactionReceipt::with([
            'transfer', 'transfer.fromBranch', 'items.transferItem', 'items.item'
        ])->latest()->paginate(10);

        return view('inventory.transactions.receipts.index', compact('receipts'));
    }

    public function create()
    {
        $this->authorize('create', TransactionReceipt::class);

        $transfers = TransactionTransfer::doesntHave('receipt')
            ->with(['items.item'])
            ->where('Status', Transfers::InTransit)
            ->get();

        $users = User::all();

        return view('inventory.transactions.receipts.create', compact('transfers', 'users'));
    }

    public function store(TransactionReceiptRequest $request)
    {
        $this->authorize('create', TransactionReceipt::class);
        $validatedData = $request->validated();
        $items = $validatedData['items'] ?? [];
        unset($validatedData['items']);

        try {
            $receipt = $this->service->createReceipt($validatedData, $items);
            return redirect()->route('transactionsreceipts.index')->with('success', 'Transaction receipt posted successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $transferId = $validatedData['TransferID'] ?? null;
            $transfer = TransactionTransfer::find($transferId);
            $branchId = $transfer?->ToBranch;

            $itemsWithDetails = collect($items)->map(function ($item) use ($branchId) {
                $itemModel = \App\Models\Inventory\ItemMasterList::find($item['item']);
                $item['item_name'] = $itemModel->ItemName ?? 'Unknown';

                $storeOptions = \App\Models\Inventory\Store::where('BranchID', $branchId)
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

        $receipt = TransactionReceipt::with(['transfer', 'items.item', 'receivedBy'])->findOrFail($id);

        return view('inventory.transactions.receipts.show', compact('receipt'));
    }

    public function destroy($id)
    {
        $this->authorize('destroy', TransactionReceipt::class);

        $receipt = TransactionReceipt::findOrFail($id);
        $this->service->delete($receipt);

        return redirect()->route('transactionsreceipts.index')->with('success', 'Receipt deleted.');
    }

    public function getTransferItems($id)
    {
        $transfer = TransactionTransfer::with('items.item')->findOrFail($id);
        $branchId = $transfer->ToBranch;

        $branchStores = \App\Models\Inventory\Store::where('BranchID', $branchId)
            ->select('Id', 'StoreName')
            ->get();

        $itemsWithStores = $transfer->items->map(function ($transferItem) use ($branchStores) {
            $item = $transferItem->item;

            return [
                'Item' => $transferItem->Item,
                'DispatchedQty' => $transferItem->DispatchedQty,
                'item' => $item,
                'stores' => $branchStores,
            ];
        });

        return response()->json([
            'items' => $itemsWithStores,
            'from_branch' => $branchId,
        ]);
    }
}
