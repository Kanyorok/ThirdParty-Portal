<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Http\Requests\Inventory\TransactionTransferRequest;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\InterBranchRequisition;
use App\Services\Inventory\TransactionTransferService;
use App\Http\Controllers\Inventory\InterBranchRequisitionController;
use Illuminate\Http\Request;
use App\Models\Core\Branch;

class TransactionTransfersController extends Controller
{
    protected TransactionTransferService $service;

    public function __construct(TransactionTransferService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $transfers = TransactionTransfer::all();
        return view('inventory.transactions.transfers.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $approvedRequisitions = InterBranchRequisition::where('Status', 'Approved')->get();
        $requisition = null;

        if ($request->has('requisition_id')) {
            $requisition = InterBranchRequisition::with(['fromBranch', 'toBranch', 'items.item', 'items.uom'])
                ->where('Id', $request->input('requisition_id'))
                ->first();
            if (!$requisition) {
                return redirect()->route('transactionstransfers.create')->with('error', 'Selected requisition not found.');
            }
        }

        return view('inventory.transactions.transfers.create', compact('approvedRequisitions', 'requisition'));
    }

    public function store(TransactionTransferRequest $request)
{
    //dd($request->validated());
    // Validate input data
    $validatedData = $request->validated();

    // Extract items separately
    $items = $validatedData['items'] ?? [];
    unset($validatedData['items']); // Remove items from main data array

    // Create the transfer first
    $transfer = $this->service->createTransfer($validatedData);

    // Then create items separately
    //$this->service->createTransferItems($transfer, $items);
    $transferitem = $this->service->createTransferItems($transfer, $items);

    return redirect()
        ->route('transactionstransfers.index', $transfer->Id)
        ->with('success', 'Inter-branch transfer created successfully.');
}


    public function show(TransactionTransfer $transactionTransfer)
    {
        $transactionTransfer->load('items');
        return view('inventory.transactions.transfers.show', compact('transactionTransfer'));
    }

    public function edit(TransactionTransfer $transactionTransfer)
    {
        $branches = Branch::all();
        $requisitions = InterBranchRequisition::all();
        $items = ItemMasterList::all();
        $transactionTransfer->load('items');
        return view('inventory.transactions.transfers.edit', compact('transactionTransfer'));
    }

    public function update(TransactionTransferRequest $request, TransactionTransfer $transactionTransfer)
    {
        $transfer = $this->service->update($transactionTransfer, $request->validated());

        return redirect()
            ->route('transactiontransfers.show', $transfer->Id)
            ->with('success', 'Inter-branch transfer updated successfully.');
    }

    public function destroy(TransactionTransfer $transactionTransfer)
    {
        $this->service->delete($transactionTransfer);

        return redirect()
            ->route('transactiontransfers.index')
            ->with('success', 'Inter-branch transfer deleted successfully.');
    }
}