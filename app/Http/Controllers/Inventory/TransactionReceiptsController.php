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

        return view('inventory.transactions.receipts.create', compact('transfers','users'));
    }

    public function store(TransactionReceiptRequest $request)
    {
        $this->authorize('create', TransactionReceipt::class);
        $validatedData = $request->validated();
        $items = $validatedData['items'] ?? [];
        unset($validatedData['items']);

        $receipt = $this->service->createReceipt($validatedData, $items, Auth::user());

        return redirect()->route('transactionsreceipts.index')->with('success', 'Transaction receipt posted successfully.');
    }

    public function show($id)
    {
        $this->authorize('view', TransactionReceipt::class);
        $receipt = TransactionReceipt::with(['transfer', 'items.item','receivedBy'])->findOrFail($id);
        return view('inventory.transactions.receipts.show', compact('receipt'));
    }

    public function destroy($id)
    {
        $this->authorize('destroy', TransactionReceipt::class);
        $receipt = TransactionReceipt::findOrFail($id);
        $this->service->deleteReceipt($receipt);
        return redirect()->route('transactionsreceipts.index')->with('success', 'Receipt deleted.');
    }

   public function getTransferItems($id)
{
    $transfer = TransactionTransfer::with('items.item')->findOrFail($id);
    $branchId = $transfer->ToBranch;

    // Fetch all stores in the branch
    $branchStores = \App\Models\Inventory\Store::where('BranchID', $branchId)
        ->select('Id', 'StoreName')
        ->get();

    $itemsWithStores = $transfer->items->map(function ($transferItem) use ($branchStores) {
        $item = $transferItem->item;

        return [
            'Item' => $transferItem->Item,
            'DispatchedQty' => $transferItem->DispatchedQty,
            'item' => $item,
            'stores' => $branchStores, // Attach all stores from the branch
        ];
    });

    return response()->json([
        'items' => $itemsWithStores,
        'from_branch' => $branchId,
    ]);
}

}


