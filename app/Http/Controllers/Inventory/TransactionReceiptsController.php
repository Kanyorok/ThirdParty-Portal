<?php

namespace App\Http\Controllers\Inventory;

use App\Enums\Inventory\Transfers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransactionReceiptRequest;
use App\Models\Auth\User;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\Store;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\TransactionTransfer;
use App\Services\Inventory\TransactionReceiptService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

        $transfer = TransactionTransfer::findOrFail($validatedData['TransferID']);

        if (Auth::user()->BranchID !== $transfer->ToBranch) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['TransferID' => 'You are not authorized to receive this transfer.']);
        }


        try {
            $receipt = $this->service->createReceipt($validatedData, $items);
            return redirect()
                ->route('transactionsreceipts.index')
                ->with('success', 'Transaction receipt posted successfully.');
        } catch (ValidationException $e) {
            $branchId = $transfer->ToBranch;

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
        $transfer = TransactionTransfer::with([
            'items.item.price',
            'items.item.uom',
            'ToBranch'
        ])->findOrFail($id);

        $branchId = $transfer->ToBranch;

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
