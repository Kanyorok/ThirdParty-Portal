<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\TransactionTransferRequest;
use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Inventory\InterBranchRequisition;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\TransactionTransfer;
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

                $stock = StockItem::where('ItemID', $itemId)
                    ->where('Branch', $branch)
                    ->first();

                if (!$stock || $stock->CurrentQty < $qty) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "Insufficient stock for ItemID {$itemId} in Branch {$branch}.",
                    ], 422);
                }
            }

            $transfer = $this->service->createTransfer($validatedData);
            $this->service->createTransferItems($transfer, $items);

        return redirect()
        ->route('transactionstransfers.index')
        ->with('success', 'Transfer created successfully.');

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($Id)
    {
        $this->authorize('view', TransactionTransfer::class);
        $transferitem = TransactionTransfer::with([
            'fromBranch', 'toBranch', 'creator', 'items.item', 'transferredBy'
        ])->findOrFail($Id);

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

    /**
     * Fetch requisitions by type (interbranch or procurement)
     */
    public function getRequisitionsByType($type)
    {
        if ($type === 'interbranch') {
            $requisitions = InterBranchRequisition::where('Status', 'Ap')
                ->whereDoesntHave('transfer')
                ->get();
        } elseif ($type === 'procurement') {
            $statusIds = DB::table('t_CodeDetails')
                ->where('CodeID', 'RequisitionStatus')
                ->where('Description', 'Approved')
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

    /**
     * Fetch requisition details including items + UOM info
     */
    public function getRequisitionDetails(Request $request, $id)
    {
        try {
            $type = $request->query('type');

            if ($type === 'interbranch') {
                $requisition = InterBranchRequisition::with([
                    'fromBranch',
                    'toBranch',
                    'items.item.price',
                    'items.item.uom'
                ])->findOrFail($id);

                $items = $requisition->items->map(function ($item) {
                    return [
                        'Id'          => $item->Id,
                        'Item'        => $item->Item,
                        'ItemCode'    => $item->item?->ItemCode ?? '',
                        'ItemName'    => $item->item?->ItemName ?? '',
                        'UnitCost'    => $item->item?->price?->ActualPrice ?? 0,
                        'UOM'         => $item->UOM ?? $item->item?->UOM,
                        'UOMCode'     => $item->item?->uom?->Code ?? 'N/A',
                        'PriceID'     => $item->item?->ItemPrice,
                        'ApprovedQty' => $item->ApprovedQty ?? $item->Quantity,
                        'DispatchedQty'=> $item->DispatchedQty ?? null,
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
                $requisition = Requisitions::with([
                    'requisitionLines.item.price',
                    'requisitionLines.item.uom'
                ])->findOrFail($id);

                $branch = $requisition->BranchID ? Branch::find($requisition->BranchID) : null;

                $items = $requisition->requisitionLines->map(function ($line) {
                    return [
                        'Id'          => $line->Id,
                        'Item'        => $line->Item,
                        'ItemCode'    => $line->item?->ItemCode ?? '',
                        'ItemName'    => $line->item?->ItemName ?? '',
                        'UnitCost'    => $line->ExpectedPrice,
                        'UOM'         => $line->UOM ?? $line->item?->UOM,
                        'UOMCode'     => $line->item?->uom?->Code ?? 'N/A',
                        'PriceID'     => $line->ExpectedPrice,
                        'ApprovedQty' => $line->Quantity,
                        'DispatchedQty'=> null,
                    ];
                });

                return response()->json([
                    'Id'          => $requisition->Id,
                    'from_branch' => null,
                    'to_branch'   => $branch,
                    'items'       => $items,
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
