<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransactionTransferRequest;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\StockGRNLedger;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Inventory\TransactionTransferItem;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\Requisitions;
use App\Services\Inventory\TransactionTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionTransfersController extends Controller
{
    protected TransactionTransferService $service;

    public function __construct(TransactionTransferService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', TransactionTransfer::class);
        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $isHeadOffice = $currentBranch->IsHeadOffice ?? false;

        $allTransfers = TransactionTransfer::with(['items.item', 'fromBranch', 'toBranch', 'transferStatus', 'transferredBy'])
            ->where(function ($q) use ($branchId) {
                $q->where('FromBranch', $branchId)
                    ->orWhere('ToBranch', $branchId);
            })
            ->get();

        $incomingTransfers = TransactionTransfer::with(['items.item', 'fromBranch', 'toBranch', 'transferStatus', 'transferredBy'])
            ->where('ToBranch', $branchId)
            ->get();

        $outgoingTransfers = TransactionTransfer::with(['items.item', 'fromBranch', 'toBranch', 'transferStatus', 'transferredBy'])
            ->where('FromBranch', $branchId)
            ->get();

        $branches = $isHeadOffice ? Branch::all() : collect();

        return view('inventory.transactions.transfers.index', compact(
            'allTransfers',
            'incomingTransfers',
            'outgoingTransfers',
            'isHeadOffice',
            'currentBranch',
            'branches'
        ));
    }

    public function create(Request $request)
    {
        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $this->authorize('create', TransactionTransfer::class);

        $currentUser = $request->user();

        $users = User::whereHas('employee', function ($q) use ($branchId) {
            $q->where('BranchId', $branchId);
        })->get();

        return view('inventory.transactions.transfers.create', compact('users', 'currentUser'));
    }

    public function store(TransactionTransferRequest $request)
    {
        $this->authorize('create', TransactionTransfer::class);

        $validatedData = $request->validated();
        $items = $validatedData['items'] ?? [];
        unset($validatedData['items']);

        $currentUser = $request->user();
        $currentBranch = $currentUser->branch;
        $isHQ = $currentBranch && $currentBranch->IsHQ;
        $requisitionType = $validatedData['RequisitionType'] ?? null;

        if ($requisitionType === 'procurement') {
            if (! $isHQ) {
                return redirect()->back()->withInput()->with('error', 'Only Headquarters can create procurement transfers.');
            }

            $requisition = Requisitions::with('requestingBranch')->findOrFail($validatedData['RequisitionId']);

            $approvedStatusId = CodeDetail::where('CodeID', 'RequisitionStatus')
                ->where('Value', 'Ap')
                ->value('Id');
            if ($requisition->StatusID != $approvedStatusId) {
                throw new \Exception('Only approved requisitions can be transferred.');
            }

            $validatedData['ToBranch'] = $requisition->requestingBranch?->Id 
          ?? throw new \Exception('Requisition has no requesting branch.');
            $validatedData['FromBranch'] = $currentBranch->Id;
            foreach ($items as $index => $itemData) {
                $itemId = $itemData['item'];
                $dispatched = (float) ($itemData['dispatched_qty'] ?? 0);
                $line = $requisition->requisitionLines()->where('Item', $itemId)->first();
                if (! $line) {
                    throw new \Exception("Item ID {$itemId} not found in the selected requisition.");
                }
                $alreadyTransferred = TransactionTransferItem::whereHas('transfer', function ($q) use ($validatedData) {
                    $q->where('RequisitionType', 'procurement')
                      ->where('RequisitionId', $validatedData['RequisitionId'])
                      ->where('Status', '!=', 're');
                })->where('Item', $itemId)->sum('DispatchedQty');

                $remaining = max(0, $line->Quantity - $alreadyTransferred);
                if ($dispatched > $remaining) {
                    throw new \Exception("Item {$line->item->ItemName} requested quantity {$dispatched} exceeds remaining requisition quantity ({$remaining}).");
                }
                $availableStock = StockGRNLedger::where('ItemNo', $itemId)
                    ->where('Branch', $currentBranch->Id)
                    ->where('RemainingQTY', '>', 0)
                    ->sum('RemainingQTY');

                if ($dispatched > $availableStock) {
                    throw new \Exception("Insufficient stock at {$currentBranch->Name} for item {$line->item->ItemName}. Available: {$availableStock}, Requested: {$dispatched}.");
                }

                $items[$index]['remaining_qty'] = $remaining;
                $items[$index]['available_stock'] = $availableStock;
            }
        }

        $validatedData['is_hq'] = $isHQ;

        DB::beginTransaction();

        try {
            $transfer = $this->service->createTransfer($validatedData);

            foreach ($items as &$item) {
                $item['is_hq'] = $isHQ;
                if ($requisitionType === 'procurement' && $isHQ) {
                    $item['batch_allocation'] = null; // HQ uses FIFO
                }
            }

            $this->service->createTransferItems($transfer, $items);

            DB::commit();

            return redirect()
                ->route('transactionstransfers.index')
                ->with('success', 'Transfer created successfully.');
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating transfer: ' . $e->getMessage());
        }
    }

    public function show($Id)
    {
        $this->authorize('view', TransactionTransfer::class);
        $transferitem = TransactionTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items.item',
            'transferredBy',
        ])->findOrFail($Id);

        return view('inventory.transactions.transfers.show', compact('transferitem'));
    }

    public function edit(Request $request, $Id)
    {
        $currentBranch = $request->user()->branch;
        $branchId = $currentBranch->Id;
        $this->authorize('update', TransactionTransfer::class);

        $branches = Branch::all();
        $itemsMasterList = ItemMasterList::all();
        $users = User::whereHas('employee', function ($q) use ($branchId) {
            $q->where('BranchId', $branchId);
        })->get();

        $transferitem = TransactionTransfer::with([
            'fromBranch',
            'toBranch',
            'creator',
            'items.item',
            'requisition',
        ])->findOrFail($Id);

        return view('inventory.transactions.transfers.edit', compact('transferitem', 'branches', 'itemsMasterList', 'users'));
    }

   public function update(TransactionTransferRequest $request, $Id)
    {
        $this->authorize('update', TransactionTransfer::class);
        $transactionTransfer = TransactionTransfer::findOrFail($Id);

        if ($transactionTransfer->Status !== 'P') {
            return redirect()->back()->with('error', 'Only pending transfers can be updated.');
        }

        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        DB::transaction(function () use ($transactionTransfer, $validated, $items) {
            $transactionTransfer->update($validated);
            $transactionTransfer->items()->delete();
            foreach ($items as $itemData) {
                TransactionTransferItem::create([
                    'TransferId'      => $transactionTransfer->Id,
                    'Item'            => $itemData['item'],
                    'ApprovedQty'     => $itemData['approved_qty'],
                    'DispatchedQty'   => $itemData['dispatched_qty'],
                    'UOM'             => $itemData['uom'],
                    'UnitCost'        => $itemData['unit_cost'] ?? null,
                    'BatchAllocation' => $itemData['batch_allocation'] ?? null,
                    'Remarks'         => $itemData['remarks'] ?? null,
                    'CreatedBy'       => $transactionTransfer->CreatedBy, 
                    'ModifiedBy'      => auth()->id(),
                    'CreatedOn'       => $transactionTransfer->CreatedOn,
                    'ModifiedOn'      => now(),
                ]);
            }
        });

        return redirect()->route('transactionstransfers.index')
                        ->with('success', 'Transfer updated successfully.');
    }
    public function getGRNBatches(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required|exists:t_Items,Id',
                'branch_id' => 'required|exists:t_Branches,Id',
            ]);

            $itemId = $request->input('item_id');
            $branchId = $request->input('branch_id');

            $batches = $this->service->getAvailableGRNBatches($itemId, $branchId);

            return response()->json([
                'success' => true,
                'batches' => $batches,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load GRN batches: ' . $e->getMessage(),
            ], 500);
        }
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

    public function getRequisitionsByType($type, Request $request)
    {
        $currentBranch = $request->user()->branch;
        if (! $currentBranch instanceof Branch) {
            return response()->json(['error' => 'Branch not found'], 404);
        }

        $branchId = $currentBranch->Id;

        if ($type === 'interbranch') {
            $requisitions = InterBranchRequisition::with(['fromBranch', 'toBranch'])
                ->where('Status', 'Ap')
                ->where('FromBranch', $branchId)
                ->whereDoesntHave('transfer')
                ->get()
                ->map(function ($req) {
                    return [
                        'Id'         => $req->Id,
                        'ReqNo'      => $req->ReqNo,
                        'to_branch'  => $req->toBranch,
                    ];
                });

            return response()->json($requisitions);
        }

        if ($type === 'procurement') {
            if (! $currentBranch->IsHQ) {
                return response()->json([], 403);
            }

            $approvedStatusId = CodeDetail::where('CodeID', 'RequisitionStatus')
                ->where('Value', 'Ap')
                ->value('Id');

            if (! $approvedStatusId) {
                return response()->json(['error' => 'Approved status not configured'], 500);
            }

            $requisitions = Requisitions::with('requestingBranch')
                ->where('StatusID', $approvedStatusId)
                ->whereDoesntHave('transfer')
                ->orderByDesc('CreatedOn')
                ->get()
                ->map(function ($req) {
                    return [
                        'Id'             => $req->Id,
                        'RequisitionNo'  => $req->RequisitionNo,
                        'branch'         => $req->requestingBranch,
                    ];
                });

            return response()->json($requisitions);
        }

        return response()->json([], 400);
    }

    public function getRequisitionDetails(Request $request, $id)
    {
        try {
            $type = $request->query('type');

            if ($type === 'interbranch') {
                $requisition = InterBranchRequisition::with([
                    'fromBranch',
                    'toBranch',
                    'items.item.price',
                    'items.item.uom',
                ])->findOrFail($id);

                $items = $requisition->items->map(function ($item) {
                    return [
                        'Id'            => $item->Id,
                        'Item'          => $item->Item,
                        'ItemCode'      => $item->item?->ItemCode ?? '',
                        'ItemName'      => $item->item?->ItemName ?? '',
                        'UnitCost'      => $item->item?->price?->ActualPrice ?? 0,
                        'UOM'           => $item->UOM ?? $item->item?->UOM,
                        'UOMCode'       => $item->item?->uom?->Code ?? 'N/A',
                        'PriceID'       => $item->item?->ItemPrice,
                        'ApprovedQty'   => $item->ApprovedQty ?? $item->Quantity,
                        'DispatchedQty' => $item->DispatchedQty ?? null,
                        
                    ];
                });

                return response()->json([
                    'Id'          => $requisition->Id,
                    'from_branch' => $requisition->fromBranch,
                    'to_branch'   => $requisition->toBranch,
                    'items'       => $items,
                ]);
            }
            if ($type === 'procurement') {
                $currentBranch = $request->user()->branch;
                if (! $currentBranch || ! $currentBranch->IsHQ) {
                    return response()->json(['error' => 'Only HQ can access procurement requisitions'], 403);
                }

                $requisition = Requisitions::with([
                    'requestingBranch',
                    'requisitionLines.item.price',
                    'requisitionLines.item.uom',
                    'requisitionLines.uom'
                ])->findOrFail($id);

                $approvedStatusId = CodeDetail::where('CodeID', 'RequisitionStatus')
                    ->where('Value', 'Ap')
                    ->value('Id');
                if ($requisition->StatusID != $approvedStatusId) {
                    return response()->json(['error' => 'Only approved requisitions can be transferred'], 422);
                }
                $currentBranchId = $currentBranch->Id;
                $items = [];

                foreach ($requisition->requisitionLines as $line) {
                    $alreadyTransferred = TransactionTransferItem::whereHas('transfer', function ($q) use ($id) {
                        $q->where('RequisitionType', 'procurement')
                          ->where('RequisitionId', $id)
                          ->where('Status', '!=', 're');
                    })->where('Item', $line->Item)->sum('DispatchedQty');

                    $remainingQty = max(0, $line->Quantity - $alreadyTransferred);
                    $availableStock = StockGRNLedger::where('ItemNo', $line->Item)
                        ->where('Branch', $currentBranchId)
                        ->where('RemainingQTY', '>', 0)
                        ->sum('RemainingQTY');

                    $items[] = [
                        'Id'             => $line->Id,
                        'Item'           => $line->Item,
                        'ItemCode'       => $line->item->ItemCode ?? '',
                        'ItemName'       => $line->item->ItemName ?? '',
                        'UnitCost'       => $line->item->price->ActualPrice ?? 0,
                        'UOM'            => $line->UOM,
                        'UOMCode'        => $line->uom->Code ?? 'N/A',
                        'PriceID'        => $line->item->ItemPrice ?? null,
                        'ApprovedQty'    => $line->Quantity,
                        'RemainingQty'   => $remainingQty,
                        'AvailableStock' => $availableStock,
                        'DispatchedQty'  => 0,
                    ];
                }

                return response()->json([
                    'Id'            => $requisition->Id,
                    'requisition_no'=> $requisition->RequisitionNo,
                    'from_branch'   => $currentBranch, 
                    'to_branch'     => $requisition->requestingBranch,
                    'items'         => $items,
                ]);
            }

            return response()->json(['error' => 'Invalid type'], 400);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}