<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransferReceipt;
use App\Http\Requests\Inventory\TransactionReceiptRequest ;
use App\Services\Inventory\TransactionTransferService;
use Illuminate\Http\Request;

class TransactionReceiptsController extends Controller
{
    protected TransactionTransferService $service;

    public function __construct(TransactionTransferService $service)
    {
        $this->service = $service;
    }

    // INDEX: Show all receipts
    public function index()
    {
        $receipts = TransferReceipt::with(['transfer', 'item'])->latest()->paginate(10);
        return view('inventory.transactions.receipts.index', compact('receipts'));
    }

    // CREATE: Display receipt form with transfers
    public function create()
    {
        $transfers = TransactionTransfer::with('items')->get();
        return view('inventory.transactions.receipts.create', compact('transfers'));
    }

    // STORE: Save new receipt
    public function store(TransactionReceiptRequest  $request)
    {
        $validatedData = $request->validated();
        $items = $validatedData['items'] ?? [];
        unset($validatedData['items']);

        $receipt = $this->service->createReceipt($validatedData);
        $this->service->createReceiptItems($receipt, $items);

        return redirect()->route('transactionsreceipts.index')->with('success', 'Goods receipt posted successfully!');
    }

    // SHOW: Display single receipt
    public function show($id)
    {
        $receipt = TransactionReceipt::with(['transfer', 'items'])->findOrFail($id);
        return view('inventory.transactions.receipts.show', compact('receipt'));
    }

    // EDIT: Show edit form
    public function edit($id)
    {
        $receipt = TransactionReceipt::with(['transfer', 'items'])->findOrFail($id);
        return view('inventory.transactions.receipts.edit', compact('receipt'));
    }

    // UPDATE: Process edit form
    public function update(TransactionReceiptRequest  $request, TransactionReceipt $transactionReceipt)
    {
        $receipt = $this->service->updateReceipt($transactionReceipt, $request->validated());

        return redirect()->route('transactionsreceipts.index')->with('success', 'Goods receipt updated successfully!');
    }

    // DELETE: Remove receipt
    public function destroy($id)
    {
        $receipt = TransactionReceipt::findOrFail($id);
        $this->service->deleteReceipt($receipt);
        return redirect()->route('transactionsreceipts.index')->with('success', 'Goods receipt deleted successfully.');
    }
    public function getTransferItems($Id)
{
    $transfer = TransactionTransfer::with('items.item')->findOrFail($Id);
    return response()->json($transfer);
}

}
