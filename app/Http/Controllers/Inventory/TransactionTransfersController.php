<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\InterBranchRequisition;
use App\Http\Requests\Inventory\TransactionTransferRequest;
use App\Services\Inventory\TransactionTransferService;
use Illuminate\Http\Request;
use App\Models\Core\Branch;
use Illuminate\Support\Facades\Auth;
use App\Enums\Inventory\Transfers;

class TransactionTransfersController extends Controller
{
    protected TransactionTransferService $service;

    public function __construct(TransactionTransferService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $transfers = TransactionTransfer::with(['items.item'])->latest()->get();
        return view('inventory.transactions.transfers.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', TransactionTransfer::class);
        $approvedRequisitions = InterBranchRequisition::where('Status', 'Ap')->get();
        $requisition = null;

        if ($request->has('requisition_id')) {
            $requisition = InterBranchRequisition::with(['fromBranch', 'toBranch', 'items.item'])
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
    $this->authorize('create', TransactionTransfer::class);
    $validatedData = $request->validated();
    $items = $validatedData['items'] ?? [];
    unset($validatedData['items']);

    $transfer = $this->service->createTransfer($validatedData);

    
    $this->service->createTransferItems($transfer, $items);

    return redirect()
        ->route('transactionstransfers.index')
        ->with('success', 'Transfer created and submitted for approval.');
}


    public function approve(TransactionTransfer $transactionTransfer)
    {
        $this->service->approve($transactionTransfer->Id);
        return redirect()->back()->with('success', 'Transfer approved and set to In Transit.');
    }

    public function reject(TransactionTransfer $transactionTransfer, Request $request)
{
    $reason = $request->input('reason'); 
    $this->service->reject($transactionTransfer->Id, $reason);

    return redirect()
        ->back()
        ->with('success', 'Transfer rejected successfully.');
}


    public function show($Id)
    {
        $this->authorize('view', TransactionTransfer::class);
        $transferitem = TransactionTransfer::with(['fromBranch', 'toBranch', 'creator', 'items.item'])->findOrFail($Id);
        return view('inventory.transactions.transfers.show', compact('transferitem'));
    }

    public function edit($Id)
    {
        $this->authorize('update', TransactionTransfer::class);
        $branches = Branch::all();
        $approvedRequisitions = InterBranchRequisition::where('Status', 'Ap')->get();
        $itemsMasterList = ItemMasterList::all();

        $transferitem = TransactionTransfer::with([
            'fromBranch', 'toBranch', 'creator', 'items.item', 'requisition'
        ])->findOrFail($Id);

        return view('inventory.transactions.transfers.edit', compact('transferitem', 'branches', 'approvedRequisitions', 'itemsMasterList'));
    }

    

    public function update(TransactionTransferRequest $request, $Id)
    {
    $this->authorize('update', TransactionTransfer::class);
    $transactionTransfer = TransactionTransfer::findOrFail($Id); 
    $this->service->update($transactionTransfer, $request->validated());
    return redirect()
        ->route('transactionstransfers.index', $transactionTransfer->Id)
        ->with('success', 'Transfer updated.');
    }


    public function destroy($Id)
    {
        $this->authorize('destroy', TransactionTransfer::class);
        $transfer = TransactionTransfer::findOrFail($Id);
        $this->service->delete($transfer);
        return redirect()->route('transactionstransfers.index')->with('success', 'Transfer deleted.');
    }
}
