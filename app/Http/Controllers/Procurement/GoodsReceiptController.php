<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Inventory\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoodsReceiptController extends Controller
{

    public function index()
    {
        $this->authorize('viewAny', GoodsReceipt::class);
        // Get only POs not used in GoodsReceipts

        $goodsReceipts = GoodsReceipt::with('receiver', 'supplier')
            ->where('InspectionStatus', PostingEnum::Draft)
            ->selectRaw('MIN(id) as id') // get one row per GRN
            ->groupBy('GRNID')
            ->get()
            ->map(function ($receipt) {
                return GoodsReceipt::with('receiver', 'supplier')->find($receipt->id);
            });


        return view('procurement.goodreceipts.index', compact('goodsReceipts'));
    }

    public function create()
    {
        $this->authorize('create', GoodsReceipt::class);
        $usedOrderNos = DB::connection('sqlsrv')
            ->table('t_GoodsReceipts')
            ->distinct()
            ->pluck('POID');

        // Fetch candidate POs not yet used in GRNs, include totals for approval check
        $Orders = DB::connection('sqlsrv')
            ->table('t_Orders')
            ->whereNotIn('OrderNo', $usedOrderNos)
            ->select('Id', 'OrderNo', 'ExtOrdNum', 'AccountID', 'OrdTotIncl')
            ->orderByDesc('CreatedOn')
            ->get();

        // Keep only fully approved POs using the centralized ApprovalService
        try {
            $approvalService = app(\App\Services\Core\ApprovalService::class);
            $Orders = $Orders->filter(function ($order) use ($approvalService) {
                $poId = (int)($order->Id ?? 0);
                $amount = (float)($order->OrdTotIncl ?? 0);
                return $poId > 0 && $approvalService->isFullyApproved('purchase_order', $poId, $amount);
            })->values();
        } catch (\Throwable $e) {
            // If approval check fails, fall back to showing none rather than unapproved POs
            Log::error('GRN create: approval filter failed', ['error' => $e->getMessage()]);
            $Orders = collect();
        }

        $OrderLines = DB::connection('sqlsrv')->table('t_OrderLines as ol')
            ->join('t_items as i', 'ol.iStockCodeID', '=', 'i.Id')
            ->leftJoin('t_CodeDetails as cd', 'i.InventoryType', '=', 'cd.Id')
            ->select(
                'ol.Id',
                'ol.iOrderID',
                'ol.iStockCodeID',
                'ol.fQuantity',
                'cd.Description as InventoryType',
                'i.ItemName',
                'i.ItemDescription',
                'i.Category',
                'i.UOM'
            )
            ->get();


        $linesGrouped = $OrderLines->groupBy('iOrderID');
        foreach ($Orders as $order) {
            $order->OrderLines = $linesGrouped[$order->Id] ?? collect();
        }

        return view('procurement.goodreceipts.create', compact('Orders'));
    }


    public function store(Request $request)
    {
        $this->authorize('create', GoodsReceipt::class);
        $authUser = Auth::user();
        Log::info('Store method reached');
        Log::info('Request data:', $request->all());

        $request->validate([
            'GRNID' => 'required|string',
            'POID' => 'required|string',
            'items' => 'required|array',
            'SupplierID' => 'required',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {

                $grn = GoodsReceipt::create([
                    'GRNID'            => $request->GRNID,
                    'ReceivedDate'     => now(),
                    'ReceivedBy'       => Auth::id(),
                    'POID'             => $request->POID,
                    'SupplierId'       => $request->SupplierID,
                    'ItemNo'           => $item['ItemNo'],
                    'StoreID'          => $item['StoreID'] ?? '2',
                    'TransferTo'       => $item['TransferTo'] ?? null,
                    'TransferStatus'   => $item['TransferTo'] ?? null,
                    'POQTY'            => $item['POQTY'] ?? 0,
                    'ReceivedQTY'      => $item['ReceivedQTY'] ?? 0,
                    'TagRequired'      => isset($item['TagRequired']) ? 1 : 0,
                    'InspectionStatus' => PostingEnum::Draft,
                    'CreatedBy'        => Auth::id(),
                    'ModifiedBy'       => Auth::id(),
                ]);

                $existingStock = StockItem::where('ItemID', $grn->ItemNo)
                    ->where('Store', $grn->StoreID)
                    ->first();

                if ($existingStock) {
                    $existingStock->CurrentQty += (int) $grn->ReceivedQTY;
                    $existingStock->LastReceived = now();
                    $existingStock->ModifiedBy = Auth::id();
                    $existingStock->save();

                    Log::info("Stock updated for ItemID {$grn->ItemNo}, new qty: {$existingStock->CurrentQty}");
                } else {
                    StockItem::create([
                        'SKUCode'      => 'SKU-' . $grn->ItemNo . '-' . time(),
                        'ItemID'       => $grn->ItemNo,
                        'UOM'          => optional($grn->item)->UOM,
                        'UnitCost'     => optional($grn->item)->UnitCost ?? 0,
                        'Store'        => $grn->StoreID,
                        'Branch'       => $grn->TransferTo ?? $authUser->BranchId ?? null,
                        'CurrentQty'   => (int) $grn->ReceivedQTY,
                        'Min'          => 0,
                        'Reorder'      => 0,
                        'Max'          => 0,
                        'LastReceived' => now(),
                        'Status'       => true,
                        'CreatedBy'    => Auth::id(),
                        'CreatedOn'    => now(),
                        'ModifiedBy'   => Auth::id(),
                        'ModifiedOn'   => now(),
                    ]);

                    Log::info("New StockItem created for ItemID {$grn->ItemNo}");
                }
            }

            DB::commit();

            return redirect()
                ->route('procurementreceipts.index')
                ->with('success', 'Goods receipt saved and stock updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Goods Receipt: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to save GRN: ' . $e->getMessage()]);
        }
    }


    public function fetchLinesByGRN($grnId, $poId)
    {
        $items = GoodsReceipt::where('GRNID', $grnId)
            ->where('POID', $poId)
            ->get();

        return response()->json($items);
    }

    public function updateLine(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:t_GoodsReceipts,id',
            'items.*.ReceivedQTY' => 'required|numeric|min:0',
            'items.*.TransferTo' => 'nullable|string',
            'items.*.TagRequired' => 'required|boolean',
        ]);

        foreach ($request->items as $item) {
            $line = GoodsReceipt::find($item['id']);
            $line->ReceivedQTY = $item['ReceivedQTY'];
            $line->TransferTo = $item['TransferTo'];
            $line->TagRequired = $item['TagRequired'];
            $line->ModifiedOn = now();
            $line->save();
        }

        return redirect()->route('procurementreceipts.index')->with('success', 'All GRN line items updated.');
    }

    public function destroy($grnId, $poId)
    {
        $deleted = GoodsReceipt::where('GRNID', $grnId)
            ->where('POID', $poId)
            ->update([
                'DeletedBy' => Auth::id(),
                'DeletedOn' => now(),
            ]);

        if ($deleted) {
            return redirect()->route('procurementreceipts.index')->with('success', 'GRN items soft-deleted successfully.');
        } else {
            return redirect()->route('procurementreceipts.index')->with('error', 'No records found to delete.');
        }
    }


    public function postReceipt(Request $request)
    {
        $grnId = $request->input('grn_id');
        $poId = $request->input('po_id');

        $grnLines = GoodsReceipt::where('GRNID', $grnId)
            ->where('POID', $poId)
            ->get();

        if ($grnLines->isEmpty()) {
            return response()->json(['error' => 'GRN lines not found.'], 404);
        }

        // Update TransferStatus to "Posted"
        foreach ($grnLines as $line) {
            $line->InspectionStatus = PostingEnum::Posted;
            $line->save();

            // Insert into t_StockTransactions
            // DB::table('t_StockTransactions')->insert([
            //     'TransactionDate' => now(),
            //     'GRNID' => $grnId,
            //     'POID' => $poId,
            //     'ItemNo' => $line->ItemNo,
            //     'StoreID' => $line->StoreID,
            //     'Quantity' => $line->ReceivedQTY,
            //     'TransactionType' => 'Receipt',
            //     'CreatedBy' => auth()->id(),
            //     'CreatedOn' => now(),
            // ]);
        }

        return response()->json(['message' => 'Receipt posted and transactions recorded.']);
    }
}
