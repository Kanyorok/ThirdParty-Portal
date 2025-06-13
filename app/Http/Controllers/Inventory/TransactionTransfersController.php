<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure; // Still needed if other parts of the app use it, but not for eager loading here
use App\Http\Requests\Inventory\TransactionTransferRequest;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\InterBranchRequisition;
use App\Services\Inventory\TransactionTransferService;
use Illuminate\Http\Request;
use App\Models\Core\Branch;
use Illuminate\Support\Facades\Auth; // Make sure Auth is imported if used in blade for transferredBy

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
        $approvedRequisitions = InterBranchRequisition::where('Status', 'Ap')->get();
        $requisition = null;

        if ($request->has('requisition_id')) {
            $requisition = InterBranchRequisition::with([
                'fromBranch',
                'toBranch',
                'items.item' 
            ])
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
        $validatedData = $request->validated();
        $items = $validatedData['items'] ?? [];
        unset($validatedData['items']);
        $transfer = $this->service->createTransfer($validatedData);

        $this->service->createTransferItems($transfer, $items);

        return redirect()
            ->route('transactionstransfers.index')
            ->with('success', 'Inter-branch transfer created successfully.');
    }

    public function show($Id)
    {
        $transferitem = TransactionTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items.item', 
        ])->findOrFail($Id);

        return view('inventory.transactions.transfers.show', compact('transferitem'));
    }

    public function edit($Id)
    {
        $branches = Branch::all();
        $approvedRequisitions = InterBranchRequisition::where('Status', 'Ap')->get();
        $itemsMasterList = ItemMasterList::all(); 

        $transferitem = TransactionTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items.item', 
            'requisition',
        ])->findOrFail($Id);

        return view('inventory.transactions.transfers.edit', compact('transferitem', 'branches', 'approvedRequisitions', 'itemsMasterList'));
    }

    public function update(TransactionTransferRequest $request, TransactionTransfer $transactionTransfer)
    {
        $transfer = $this->service->update($transactionTransfer, $request->validated());

        return redirect()
            ->route('transactionstransfers.show', $transfer->Id)
            ->with('success', 'Transfer updated successfully.');
    }

    public function destroy($Id)
    {
        $transfer = TransactionTransfer::findOrFail($Id);
        $this->service->delete($transfer);
        return redirect()->route('transactionstransfers.index')
            ->with('success', 'Inter-branch transfer deleted successfully.');
    }
}