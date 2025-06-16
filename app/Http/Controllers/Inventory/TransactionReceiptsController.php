<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionReceipt;
use App\Http\Requests\Inventory\TransactionReceiptRequest;
use App\Services\Inventory\TransactionReceiptService;
use Illuminate\Http\Request;

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
            'transfer',
            'transfer.fromBranch',
            'items.transferItem',
            'items.item'
        ])->latest()->paginate(10);
        return view('inventory.transactions.receipts.index', compact('receipts'));
    }

    public function create()
    {
        $transfers = TransactionTransfer::with(['items.item'])->get();
        return view('inventory.transactions.receipts.create', compact('transfers'));
    }

    public function store(TransactionReceiptRequest $request)
    {
        

        $validatedData = $request->validated();
        $items = $validatedData['items'] ?? [];
        unset($validatedData['items']);

        $receipt = $this->service->createReceipt($validatedData);
        $this->service->createReceiptItems($receipt, $items);

        return redirect()->route('transactionsreceipts.index')->with('success', 'Goods receipt posted successfully!');
    }

    public function show($id)
    {
        $receipt = TransactionReceipt::with(['transfer', 'items.item'])->findOrFail($id);
        return view('inventory.transactions.receipts.show', compact('receipt'));
    }

    public function edit($id)
    {
        $this->authorize('update', TransactionReceipt::class);
        $receipt = TransactionReceipt::with(['transfer', 'items.item'])->findOrFail($id);
        return view('inventory.transactions.receipts.edit', compact('receipt'));
    }

    public function update(TransactionReceiptRequest $request, TransactionReceipt $transactionReceipt)
    {
        try {
            $this->authorize('update', TransactionReceipt::class);
            \Log::info('Update Request Data:', $request->all());
            $validatedData = $request->validated();
            \Log::info('Validated Data:', $validatedData);
            
            $this->service->updateReceipt($transactionReceipt, $validatedData);
            
            \Log::info('Receipt after update:', $transactionReceipt->toArray());
            return redirect()->route('transactionsreceipts.index')->with('success', 'Goods receipt updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Update Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to update receipt: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize('destroy', TransactionReceipt::class);
        $receipt = TransactionReceipt::findOrFail($id);
        $this->service->deleteReceipt($receipt);
        return redirect()->route('transactionsreceipts.index')->with('success', 'Goods receipt deleted successfully.');
    }

   public function getTransferItems($id)
{
    $transfer = TransactionTransfer::with('items.item')->findOrFail($id);

    return response()->json([
        'items' => $transfer->items,
        'from_branch' => $transfer->FromBranch,
    ]);
}

}
