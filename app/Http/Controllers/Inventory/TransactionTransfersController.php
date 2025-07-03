<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Procurement\Requisitions;
use App\Models\Procurement\RequisitionLine;
use App\Http\Requests\Inventory\TransactionTransferRequest;
use App\Services\Inventory\TransactionTransferService;
use Illuminate\Http\Request;
use App\Models\Core\Branch;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

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
        $users = User::all();
        return view('inventory.transactions.transfers.create', compact('users'));
    }

    public function store(TransactionTransferRequest $request)
{
    $this->authorize('create', TransactionTransfer::class);

    $validatedData = $request->validated();
    $items = $validatedData['items'] ?? [];
    unset($validatedData['items']);

    try {
        foreach ($items as $item) {
            $itemId = $item['item'];
            $qty = $item['dispatched_qty'];

            $branch = $validatedData['RequisitionType'] === 'procurement'
                ? app(TransactionTransferService::class)->getHQBranchId()
                : $validatedData['FromBranch'];

            $stock = \App\Models\Inventory\StockItem::where('ItemID', $itemId)
                ->where('Branch', $branch)
                ->first();

            if (!$stock || $stock->CurrentQty < $qty) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Insufficient stock for ItemID {$itemId} in Branch {$branch}.",
                ], 422);
            }
        }

        $transfer = $this->service->createTransfer($validatedData);
        $this->service->createTransferItems($transfer, $items);

        return response()->json([
            'status' => 'success',
            'message' => 'Transfer created successfully.',
            'redirect' => route('transactionstransfers.index'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function show($Id)
    {
        $this->authorize('view', TransactionTransfer::class);
        $transferitem = TransactionTransfer::with(['fromBranch', 'toBranch', 'creator', 'items.item','transferredBy'])->findOrFail($Id);
        return view('inventory.transactions.transfers.show', compact('transferitem'));
    }

    public function edit($Id)
    {
        $this->authorize('update', TransactionTransfer::class);
        $branches = Branch::all();
        $itemsMasterList = ItemMasterList::all();
        $users = User::all();
        $transferitem = TransactionTransfer::with([
            'fromBranch', 'toBranch', 'creator', 'items.item', 'requisition'
        ])->findOrFail($Id);

        return view('inventory.transactions.transfers.edit', compact('transferitem', 'branches', 'itemsMasterList', 'users'));
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

    DB::transaction(function () use ($Id) {
        $transactionTransfer = TransactionTransfer::findOrFail($Id);

        $transactionTransfer->items()->delete();
        $transactionTransfer->delete();
    });

    return redirect()->route('transactionstransfers.index')->with('success', 'Transfer deleted.');
}


  public function getRequisitionsByType($type)
{
    if ($type === 'interbranch') {
        $requisitions = InterBranchRequisition::where('Status', 'Ap')
            ->whereDoesntHave('transfer')
            ->get();
    } elseif ($type === 'procurement') {
        $statusIds = DB::table('t_CodeDetails')
            ->where('CodeID', 'a')
            ->pluck('ID');

        $requisitions = Requisitions::whereIn('StatusID', $statusIds)
            ->whereNotNull('PlanRef')
            ->whereDoesntHave('transfer', function ($q) {
                $q->where('RequisitionType', 'procurement');
            })
            ->get();
    } else {
        return response()->json([], 400);
    }

    return response()->json($requisitions);
}

    public function getRequisitionDetails(Request $request, $id)
    {
        $type = $request->query('type');

        if ($type === 'interbranch') {
            $requisition = InterBranchRequisition::with(['fromBranch', 'toBranch', 'items.item'])->findOrFail($id);
            $items = $requisition->items->map(function ($item) {
                return [
                    'Item' => $item->Item,
                    'ItemCode' => $item->item->ItemCode ?? '',
                    'ItemName' => $item->item->ItemName ?? '',
                    'UOM' => $item->item->UOM, 
                    'UOMCode' => $item->item->uom->Code ?? 'N/A', 
                    'ApprovedQty' => $item->ApprovedQty,
                ];
            });

            return response()->json([
                'Id' => $requisition->Id,
                'from_branch' => $requisition->fromBranch,
                'to_branch' => $requisition->toBranch,
                'items' => $items,
            ]);
        }

        if ($type === 'procurement') {
            $requisition = Requisitions::with('requisitionLines.item')->findOrFail($id);
            $branch = $requisition->BranchID ? Branch::find($requisition->BranchID) : null;

            $items = $requisition->requisitionLines->map(function ($line) {
                return [
                    'Item' => $line->Item,
                    'ItemCode' => $line->item->ItemCode ?? '',
                    'ItemName' => $line->item->ItemName ?? $line->Description,
                    'UOM' => $line->item->UOM ?? $line->UOM,
                    'UOMCode' => $line->item->uom->Code ?? 'N/A', 
                    'ApprovedQty' => $line->Quantity,
                ];
            });

            return response()->json([
                'Id' => $requisition->Id,
                'from_branch' => null,
                'to_branch' => $branch,
                'items' => $items,
            ]);
        }

        return response()->json([], 400);
    }
}
