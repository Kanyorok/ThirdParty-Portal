<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransactionTransferRequest;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\TransactionTransfer;
use App\Models\Procurement\GoodsReceipt;
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

        // Get all transfers involving the current branch
        $allTransfers = TransactionTransfer::with(['items.item', 'fromBranch', 'toBranch', 'transferStatus', 'transferredBy'])
            ->where(function ($q) use ($branchId) {
                $q->where('FromBranch', $branchId)
                    ->orWhere('ToBranch', $branchId);
            })
            ->get();

        // Incoming transfers (to current branch)
        $incomingTransfers = TransactionTransfer::with(['items.item', 'fromBranch', 'toBranch', 'transferStatus', 'transferredBy'])
            ->where('ToBranch', $branchId)
            ->get();

        // Outgoing transfers (from current branch)
        $outgoingTransfers = TransactionTransfer::with(['items.item', 'fromBranch', 'toBranch', 'transferStatus', 'transferredBy'])
            ->where('FromBranch', $branchId)
            ->get();

        // For HQ, get all branches for filter
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

        // Get current user
        $currentUser = $request->user();

        // Get other users for dropdown (if needed for override)
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

        DB::beginTransaction();

        try {
            // Create transfer and items
            $transfer = $this->service->createTransfer($validatedData);
            $this->service->createTransferItems($transfer, $items);

            DB::commit();

            $message = 'Transfer created successfully.';

            return redirect()
                ->route('transactionstransfers.index')
                ->with('success', $message);
        } catch (Throwable $e) {
            DB::rollBack();

            $errorMessage = 'Error creating transfer: ' . $e->getMessage();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $errorMessage);
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

    public function edit($Id)
    {
        $currentBranch = $request->user()->branch;

        $branchId = $currentBranch->Id;
        $branch = Branch::findOrFail($branchId);
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

        $this->service->update($transactionTransfer, $request->validated());

        return redirect()
            ->route('transactionstransfers.index', $transactionTransfer->Id)
            ->with('success', 'Transfer updated.');
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
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        if ($type === 'interbranch') {
            $requisitions = InterBranchRequisition::with(['fromBranch', 'toBranch'])
                ->where('Status', 'Ap')
                ->where(function ($q) use ($branchId) {
                    $q->where('FromBranch', $branchId);
                })
                ->whereDoesntHave('transfer')
                ->get();
        } elseif ($type === 'procurement') {
            $requisitions = GoodsReceipt::with(['transfer', 'item'])
                ->whereNotNull('GRNID')
                ->whereDoesntHave('transfer', function ($q) {
                    $q->where('RequisitionType', 'procurement');
                })
                ->get([
                    'id',
                    'GRNID',
                    'TransferTo',
                    'ItemNo',
                    'ReceivedQTY',
                ]);
        } else {
            return response()->json([], 400);
        }

        return response()->json($requisitions);
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
                        'Id' => $item->Id,
                        'Item' => $item->Item,
                        'ItemCode' => $item->item?->ItemCode ?? '',
                        'ItemName' => $item->item?->ItemName ?? '',
                        'UnitCost' => $item->item?->price?->ActualPrice ?? 0,
                        'UOM' => $item->UOM ?? $item->item?->UOM,
                        'UOMCode' => $item->item?->uom?->Code ?? 'N/A',
                        'PriceID' => $item->item?->ItemPrice,
                        'ApprovedQty' => $item->ApprovedQty ?? $item->Quantity,
                        'DispatchedQty' => $item->DispatchedQty ?? null,
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
                $requisition = GoodsReceipt::with(['item.price', 'item.uom', 'toBranch'])
                    ->where('Id', $id)
                    ->get();

                if ($requisition->isEmpty()) {
                    return response()->json(['error' => 'No procurement requisition found'], 404);
                }

                $items = $requisition->map(function ($gr) {
                    return [
                        'Id' => $gr->id,
                        'Item' => $gr->ItemNo,
                        'ItemCode' => $gr->item?->ItemCode ?? '',
                        'ItemName' => $gr->item?->ItemName ?? '',
                        'UnitCost' => $gr->item?->price?->ActualPrice ?? 0,
                        'UOM' => $gr->item?->UOM,
                        'UOMCode' => $gr->item?->uom?->Code ?? 'N/A',
                        'PriceID' => $gr->item?->ItemPrice,
                        'ApprovedQty' => $gr->POQTY,
                        'DispatchedQty' => $gr->ReceivedQTY,
                    ];
                });

                return response()->json([
                    'Id' => $requisition->first()->GRNID,
                    'from_branch' => null,
                    'to_branch' => $requisition->first()->toBranch,
                    'items' => $items,
                ]);
            }

            return response()->json(['error' => 'Invalid type'], 400);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
