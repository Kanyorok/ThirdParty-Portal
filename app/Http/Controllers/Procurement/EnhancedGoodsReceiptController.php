<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\EnhancedGoodsReceipt;
use App\Models\Procurement\Order;
use App\Models\Procurement\OrderLines;
use App\Models\Inventory\ItemMasterList;
use App\Services\Procurement\GRN\GRNProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Enums\Core\PostingEnum;

class EnhancedGoodsReceiptController extends Controller
{
    protected $grnProcessingService;

    public function __construct(GRNProcessingService $grnProcessingService)
    {
        $this->grnProcessingService = $grnProcessingService;
    }

    /**
     * Display a listing of GRNs
     */
    public function index(Request $request)
    {
        // Quick config check for Service GL transaction code
        $serviceGlConfigured = DB::table('t_FinanceTransactionTypes')->where('Code', 'GRN-SERVICE')->exists();
        $query = EnhancedGoodsReceipt::with([
            'receiver',
            'supplier',
            'item',
            'order',
            'qualityChecker',
            'poster'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('InspectionStatus', $request->status);
        }

        if ($request->filled('processing_status')) {
            $query->where('ProcessingStatus', $request->processing_status);
        }

        if ($request->filled('item_type')) {
            $query->where('ItemType', $request->item_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('ReceivedDate', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ReceivedDate', '<=', $request->date_to);
        }

        // Group by GRNID for summary view
        $goodsReceipts = $query->selectRaw('MIN(id) as id, GRNID, POID, COUNT(*) as line_count,
                                          SUM(TotalValue) as total_value, MAX(ReceivedDate) as received_date')
            ->groupBy('GRNID', 'POID')
            ->orderByDesc('received_date')
            ->paginate(20);

        // Load full data for each GRN
        $goodsReceipts->getCollection()->transform(function ($receipt) {
            return EnhancedGoodsReceipt::with(['receiver', 'supplier', 'order'])
                ->find($receipt->id);
        });

        return view('procurement.goods-receipt.index', compact('goodsReceipts', 'serviceGlConfigured'));
    }

    /**
     * Show form to create new GRN
     */
    public function create()
    {
        // Get approved POs that don't have complete GRNs
        $availablePOs = $this->getAvailablePurchaseOrders();

        return view('procurement.goods-receipt.create', compact('availablePOs'));
    }

    /**
     * Get available purchase orders for GRN creation
     */
    public function getAvailablePurchaseOrders()
    {
        // Get POs with their order lines and items
        return DB::table('t_Orders as o')
            ->join('t_OrderLines as ol', 'o.Id', '=', 'ol.iOrderID')
            ->join('t_Items as i', 'ol.iStockCodeID', '=', 'i.Id')
            ->leftJoin('t_Suppliers as s', 'o.AccountID', '=', 's.Id')
            ->leftJoin('t_ThirdParties as tp', 's.ThirdPartyID', '=', 'tp.Id')
            ->where('o.Status', 'approved')
            ->whereRaw('ol.fQuantity > COALESCE((
                SELECT SUM(ReceivedQTY)
                FROM t_GoodsReceipts
                WHERE POID = o.Id
                AND ItemNo = ol.iStockCodeID
                AND DeletedOn IS NULL
            ), 0)')
            ->whereNull('o.DeletedOn')
            ->whereNull('ol.DeletedOn')
            ->select([
                'o.Id',
                'o.OrderNo',
                'o.AccountID',
                'tp.TradingName as SupplierName',
                'tp.ThirdPartyName as SupplierFullName'
            ])
            ->distinct()
            ->get()
            ->map(function ($order) {
                // Get order lines for this order
                $orderLines = DB::table('t_OrderLines as ol')
                    ->join('t_Items as i', 'ol.iStockCodeID', '=', 'i.Id')
                    ->where('ol.iOrderID', $order->Id)
                    ->whereNull('ol.DeletedOn')
                    ->whereRaw('ol.fQuantity > COALESCE((
                        SELECT SUM(ReceivedQTY)
                        FROM t_GoodsReceipts
                        WHERE POID = ?
                        AND ItemNo = ol.iStockCodeID
                        AND DeletedOn IS NULL
                    ), 0)', [$order->Id])
                    ->select(['ol.*', 'i.ItemName', 'i.ItemDescription'])
                    ->get();

                $order->remaining_lines = $orderLines;
                $order->supplier = (object)[
                    'thirdParty' => (object)[
                        'TradingName' => $order->SupplierName,
                        'ThirdPartyName' => $order->SupplierFullName
                    ]
                ];

                return $order;
            })
            ->filter(function ($order) {
                return $order->remaining_lines->isNotEmpty();
            })
            ->values()
            ->map(function ($order) {
                $order->remaining_lines = $order->orderLines->filter(function ($line) {
                    $receivedQty = EnhancedGoodsReceipt::where('POID', $order->Id)
                        ->where('ItemNo', $line->iStockCodeID)
                        ->whereNull('DeletedOn')
                        ->sum('ReceivedQTY');
                    return $line->fQuantity > $receivedQty;
                });
                return $order;
            })
            ->filter(function ($order) {
                return $order->remaining_lines->isNotEmpty();
            });
    }

    /**
     * Get PO details with remaining quantities for AJAX
     */
    public function getPODetails($poId): JsonResponse
    {
        try {
            $order = Order::with(['orderLines.item.itemType', 'supplier.thirdParty'])
                ->findOrFail($poId);

            $poDetails = [
                'order_no' => $order->OrderNo,
                'supplier' => [
                    'id' => $order->AccountID,
                    'name' => $order->supplier->thirdParty->TradingName ?? $order->supplier->thirdParty->ThirdPartyName ?? 'Unknown Supplier',
                ],
                'lines' => [],
            ];

            foreach ($order->orderLines as $line) {
                $receivedQty = EnhancedGoodsReceipt::where('POID', $poId)
                    ->where('ItemNo', $line->iStockCodeID)
                    ->whereNull('DeletedOn')
                    ->sum('ReceivedQTY');

                $remainingQty = $line->fQuantity - $receivedQty;

                if ($remainingQty > 0) {
                    $item = $line->item;
                    $itemType = $this->determineItemType($item);

                    $poDetails['lines'][] = [
                        'id' => $line->Id,
                        'item_id' => $line->iStockCodeID,
                        'item_name' => $item->ItemName ?? 'Unknown Item',
                        'item_description' => $item->ItemDescription ?? '',
                        'item_type' => $itemType,
                        'item_type_display' => $this->getItemTypeDisplay($itemType),
                        'ordered_qty' => $line->fQuantity,
                        'received_qty' => $receivedQty,
                        'remaining_qty' => $remainingQty,
                        'unit_price' => $line->fUnitPriceExcl,
                        'uom' => $item->uom->Name ?? 'Each',
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $poDetails,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get PO details', [
                'po_id' => $poId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load PO details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store new GRN
     */
    public function store(Request $request)
    {
        $request->validate([
            'grn_id' => 'required|string|unique:t_GoodsReceipts,GRNID',
            'po_id' => 'required|exists:t_Orders,Id',
            'supplier_id' => 'required',
            'delivery_note_ref' => 'nullable|string',
            'received_date' => 'required|date',
            'store_id' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:t_Items,Id',
            'items.*.order_line_id' => 'required|exists:t_OrderLines,Id',
            'items.*.received_qty' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.manufacture_date' => 'nullable|date',
            'items.*.requires_quality_check' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->items as $itemData) {
                $item = ItemMasterList::find($itemData['item_id']);
                $itemType = $this->determineItemType($item);
                $totalValue = $itemData['received_qty'] * $itemData['unit_price'];

                // Determine quality status
                $qualityStatus = $itemData['requires_quality_check'] ?? false
                    ? EnhancedGoodsReceipt::QUALITY_PENDING
                    : EnhancedGoodsReceipt::QUALITY_NOT_REQUIRED;

                EnhancedGoodsReceipt::create([
                    'GRNID' => $request->grn_id,
                    'POID' => $request->po_id,
                    'OrderLineID' => $itemData['order_line_id'],
                    'ReceivedDate' => $request->received_date,
                    'SupplierId' => $request->supplier_id,
                    'StoreID' => $request->store_id,
                    'ReceivedBy' => Auth::id(),
                    'ItemNo' => $itemData['item_id'],
                    'ItemType' => $itemType,
                    'POQTY' => $itemData['ordered_qty'] ?? $itemData['received_qty'], // Fallback
                    'ReceivedQTY' => $itemData['received_qty'],
                    'UnitPrice' => $itemData['unit_price'],
                    'TotalValue' => $totalValue,
                    'DeliveryNoteRef' => $request->delivery_note_ref,
                    'BatchNumber' => $itemData['batch_number'],
                    'ExpiryDate' => $itemData['expiry_date'],
                    'ManufactureDate' => $itemData['manufacture_date'],
                    'InspectionStatus' => PostingEnum::Draft,
                    'ProcessingStatus' => EnhancedGoodsReceipt::STATUS_PENDING,
                    'QualityStatus' => $qualityStatus,
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            }

            DB::commit();

            Log::info('GRN created successfully', [
                'grn_id' => $request->grn_id,
                'po_id' => $request->po_id,
                'items_count' => count($request->items),
            ]);

            return redirect()
                ->route('goods-receipt.show', ['grnId' => $request->grn_id, 'poId' => $request->po_id])
                ->with('success', 'Goods Receipt Note created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create GRN', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create GRN: ' . $e->getMessage());
        }
    }

    /**
     * Show GRN details
     */
    public function show($grnId, $poId)
    {
        $grnLines = EnhancedGoodsReceipt::with([
            'item.itemType',
            'item.uom',
            'receiver',
            'qualityChecker',
            'poster',
            'supplier.thirdParty',
            'order',
            'orderLine'
        ])
            ->byGRN($grnId)
            ->byPO($poId)
            ->get();

        if ($grnLines->isEmpty()) {
            return redirect()
                ->route('goods-receipt.index')
                ->with('error', 'GRN not found.');
        }

        $grnSummary = [
            'grn_id' => $grnId,
            'po_id' => $poId,
            'order_no' => $grnLines->first()->order->OrderNo ?? 'N/A',
            'supplier_name' => $grnLines->first()->supplier->thirdParty->TradingName ?? 'N/A',
            'received_date' => $grnLines->first()->ReceivedDate,
            'total_lines' => $grnLines->count(),
            'total_value' => $grnLines->sum('TotalValue'),
            'can_post' => $grnLines->every->canBePosted(),
            'processing_status' => $this->getGRNProcessingStatus($grnLines),
        ];

        return view('procurement.goods-receipt.show', compact('grnLines', 'grnSummary'));
    }

    /**
     * Process/Post GRN
     */
    public function processGRN(Request $request, $grnId, $poId): JsonResponse
    {
        try {
            $grnLines = EnhancedGoodsReceipt::byGRN($grnId)
                ->byPO($poId)
                ->readyForPosting()
                ->get();

            if ($grnLines->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No GRN lines are ready for posting.',
                ], 422);
            }

            $results = ['processed' => 0, 'failed' => 0, 'errors' => []];

            foreach ($grnLines as $grnLine) {
                // Validate before processing
                $validationErrors = $this->grnProcessingService->validateGRNLine($grnLine);
                if (!empty($validationErrors)) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'item_no' => $grnLine->ItemNo,
                        'errors' => $validationErrors,
                    ];
                    continue;
                }

                if ($this->grnProcessingService->processGRNLine($grnLine)) {
                    $results['processed']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'item_no' => $grnLine->ItemNo,
                        'error' => $grnLine->ProcessingErrors,
                    ];
                }
            }

            $message = "GRN processed: {$results['processed']} successful";
            if ($results['failed'] > 0) {
                $message .= ", {$results['failed']} failed";
            }

            return response()->json([
                'success' => $results['processed'] > 0,
                'message' => $message,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process GRN', [
                'grn_id' => $grnId,
                'po_id' => $poId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process GRN: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update quality check status
     */
    public function updateQualityStatus(Request $request, $grnLineId): JsonResponse
    {
        $request->validate([
            'quality_status' => 'required|in:passed,failed',
            'quality_remarks' => 'nullable|string',
        ]);

        try {
            $grnLine = EnhancedGoodsReceipt::findOrFail($grnLineId);

            if ($request->quality_status === 'passed') {
                $grnLine->markQualityPassed($request->quality_remarks);
            } else {
                $grnLine->markQualityFailed($request->quality_remarks);
            }

            return response()->json([
                'success' => true,
                'message' => 'Quality status updated successfully.',
                'data' => [
                    'quality_status' => $grnLine->QualityStatus,
                    'quality_badge' => $grnLine->quality_status_badge,
                    'can_post' => $grnLine->canBePosted(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quality status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get GRN processing dashboard data
     */
    public function dashboard()
    {
        $stats = [
            'pending_grns' => EnhancedGoodsReceipt::where('InspectionStatus', PostingEnum::Draft)->distinct('GRNID')->count(),
            'pending_quality' => EnhancedGoodsReceipt::where('QualityStatus', EnhancedGoodsReceipt::QUALITY_PENDING)->count(),
            'pending_processing' => EnhancedGoodsReceipt::pendingProcessing()->count(),
            'processed_today' => EnhancedGoodsReceipt::processed()->whereDate('PostedAt', today())->count(),
            'total_value_pending' => EnhancedGoodsReceipt::pendingProcessing()->sum('TotalValue'),
        ];

        // Get recent GRNs
        $recentGRNs = EnhancedGoodsReceipt::with(['item', 'supplier'])
            ->orderByDesc('CreatedOn')
            ->limit(10)
            ->get();

        // Get processing errors
        $processingErrors = EnhancedGoodsReceipt::where('ProcessingStatus', EnhancedGoodsReceipt::STATUS_ERROR)
            ->with(['item'])
            ->orderByDesc('ModifiedOn')
            ->limit(5)
            ->get();

        return view('procurement.goods-receipt.dashboard', compact('stats', 'recentGRNs', 'processingErrors'));
    }

    // Helper methods

    protected function determineItemType($item): string
    {
        if (!$item) {
            return EnhancedGoodsReceipt::ITEM_TYPE_STOCK;
        }

        $itemTypeName = $item->itemType->Description ?? 'Stock';

        $typeMapping = [
            'Stock' => EnhancedGoodsReceipt::ITEM_TYPE_STOCK,
            'Inventory' => EnhancedGoodsReceipt::ITEM_TYPE_STOCK,
            'Asset' => EnhancedGoodsReceipt::ITEM_TYPE_ASSET,
            'Fixed Asset' => EnhancedGoodsReceipt::ITEM_TYPE_ASSET,
            'Service' => EnhancedGoodsReceipt::ITEM_TYPE_SERVICE,
            'Non-Stock' => EnhancedGoodsReceipt::ITEM_TYPE_SERVICE,
        ];

        return $typeMapping[$itemTypeName] ?? EnhancedGoodsReceipt::ITEM_TYPE_STOCK;
    }

    protected function getItemTypeDisplay(string $itemType): string
    {
        $displays = [
            EnhancedGoodsReceipt::ITEM_TYPE_STOCK => 'Stock Item',
            EnhancedGoodsReceipt::ITEM_TYPE_ASSET => 'Asset Item',
            EnhancedGoodsReceipt::ITEM_TYPE_SERVICE => 'Service Item',
        ];

        return $displays[$itemType] ?? 'Unknown';
    }

    protected function getGRNProcessingStatus($grnLines): string
    {
        if ($grnLines->every->isProcessed()) {
            return 'All Processed';
        }

        if ($grnLines->some->isProcessed()) {
            return 'Partially Processed';
        }

        if ($grnLines->some->hasError()) {
            return 'Has Errors';
        }

        return 'Pending';
    }
}
