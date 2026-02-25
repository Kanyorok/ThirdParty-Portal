<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemMasterList;
use App\Models\Procurement\EnhancedGoodsReceipt;
use App\Models\Procurement\Order;
use App\Services\Procurement\GRN\GRNProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'poster',
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
            return EnhancedGoodsReceipt::with(['receiver', 'supplier.thirdParty.thirdParty', 'order'])
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

        // Fetch real stores from the database
        $stores = DB::table('t_Stores')
            ->where('Status', 1)
            ->whereNull('DeletedOn')
            ->select('Id', 'StoreName')
            ->orderBy('StoreName')
            ->get();

        return view('procurement.goods-receipt.create', compact('availablePOs', 'stores'));
    }

    /**
     * Get available purchase orders for GRN creation
     */
    public function getAvailablePurchaseOrders()
    {
        // Logic adapted from GoodsReceiptController::create

        // 1. Get total received quantities per PO line item from existing GRNs
        $receivedQuantities = DB::table('t_GoodsReceipts')
            ->select('POID', 'ItemNo', DB::raw('SUM(ReceivedQTY) as TotalReceived'))
            ->whereNull('DeletedOn')
            ->groupBy('POID', 'ItemNo')
            ->get()
            ->groupBy(fn ($item) => (int) $item->POID)
            ->map(function ($items) {
                return $items->keyBy(fn ($item) => (int) $item->ItemNo)->map(fn ($item) => (float) $item->TotalReceived);
            });

        // 2. Fetch all approved POs with their Supplier Details
        $Orders = DB::table('t_Orders')
            ->leftJoin('t_Branches', 't_Orders.BranchID', '=', 't_Branches.Id')
            ->leftJoin('t_Suppliers as s', 't_Orders.AccountID', '=', 's.Id')
            ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->leftJoin('t_ThirdParties as tp', 'sm.ThirdPartyId', '=', 'tp.Id')
            ->where(function ($query) {
                $query->where('t_Orders.DocStatus', 'A')  // Approved
                      ->orWhere('t_Orders.DocStatus', 'a');
            })
            ->select(
                't_Orders.Id',
                't_Orders.OrderNo',
                't_Orders.ExtOrdNum',
                't_Orders.AccountID',
                't_Orders.OrdTotIncl',
                't_Orders.BranchID',
                't_Branches.Name as BranchName',
                'tp.TradingName as SupplierName',
                'tp.ThirdPartyName as SupplierFullName'
            )
            ->orderByDesc('t_Orders.CreatedOn')
            ->get();

        // 3. Fetch all order lines with item details
        $OrderLines = DB::table('t_OrderLines as ol')
            ->join('t_items as i', 'ol.iStockCodeID', '=', 'i.Id')
            ->leftJoin('t_ItemCategories as ic', 'i.Category', '=', 'ic.Id')
            ->leftJoin('t_CodeDetails as cd', 'i.InventoryType', '=', 'cd.Id')
            ->select(
                'ol.Id',
                'ol.iOrderID',
                'ol.iStockCodeID',
                'ol.fQuantity',
                'ol.fUnitPriceExcl',
                'cd.Description as InventoryType',
                'i.ItemCode',
                'i.ItemName',
                'i.ItemDescription',
                'i.Category',
                'ic.CategoryCode',
                'i.UOM'
            )
            ->get();

        $linesGrouped = $OrderLines->groupBy('iOrderID');

        // 4. Filter orders to only include those with remaining items
        $filteredOrders = collect();

        foreach ($Orders as $order) {
            $orderLines = $linesGrouped[$order->Id] ?? collect();
            $receivedForPO = $receivedQuantities[(int) $order->Id] ?? collect();

            // Calculate remaining quantities for each line
            $linesWithRemaining = $orderLines->map(function ($line) use ($receivedForPO) {
                $received = $receivedForPO[(int) $line->iStockCodeID] ?? 0;
                $remaining = $line->fQuantity - $received;

                // Add remaining quantity to line object
                $line->fReceivedSoFar = $received;
                $line->fRemainingQty = max(0, $remaining);

                return $line;
            })->filter(function ($line) {
                // Only keep lines with remaining quantity > 0
                return $line->fRemainingQty > 0;
            });

            // Only include PO if it has remaining items
            if ($linesWithRemaining->isNotEmpty()) {
                // Map to structure expected by EnhancedGoodsReceiptController view
                $order->remaining_lines = $linesWithRemaining->values();
                // Map supplier object structure
                $order->supplier = (object)[
                    'thirdParty' => (object)[
                        'TradingName' => $order->SupplierName,
                        'ThirdPartyName' => $order->SupplierFullName,
                    ],
                ];

                $filteredOrders->push($order);
            }
        }

        return $filteredOrders;
    }

    /**
     * Get PO details with remaining quantities for AJAX
     */
    public function getPODetails($poId): JsonResponse
    {
        try {
            // 1. Fetch Order with Supplier Details using DB Query (matching getAvailablePurchaseOrders logic)
            $order = DB::table('t_Orders')
                ->leftJoin('t_Branches', 't_Orders.BranchID', '=', 't_Branches.Id')
                ->leftJoin('t_Suppliers as s', 't_Orders.AccountID', '=', 's.Id')
                ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->leftJoin('t_ThirdParties as tp', 'sm.ThirdPartyId', '=', 'tp.Id')
                ->where('t_Orders.Id', $poId)
                ->select(
                    't_Orders.Id',
                    't_Orders.OrderNo',
                    't_Orders.AccountID',
                    'tp.TradingName as SupplierName',
                    'tp.ThirdPartyName as SupplierFullName'
                )
                ->first();

            if (! $order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase Order not found.',
                ], 404);
            }

            // 2. Fetch Order Lines
            $orderLines = DB::table('t_OrderLines as ol')
                ->join('t_items as i', 'ol.iStockCodeID', '=', 'i.Id')
                ->leftJoin('t_CodeDetails as cd', 'i.InventoryType', '=', 'cd.Id')
                ->leftJoin('t_ItemTypes as it', 'i.ItemType', '=', 'it.Id')
                ->leftJoin('t_UOM as u', 'i.UOM', '=', 'u.Id')
                ->leftJoin('t_ItemCategories as ic', 'i.Category', '=', 'ic.Id')
                ->where('ol.iOrderID', $poId)
                ->whereNull('ol.DeletedOn')
                ->select(
                    'ol.Id',
                    'ol.iStockCodeID',
                    'ol.fQuantity',
                    'ol.fUnitPriceExcl',
                    'i.ItemCode',
                    'i.ItemName',
                    'i.ItemDescription',
                    'i.ItemType',
                    'it.TypeName as ItemTypeName',
                    'u.Name as UOMName',
                    'ic.CategoryCode'
                )
                ->get();

            // 3. Get Received Quantities for this PO
            $receivedQuantities = DB::table('t_GoodsReceipts')
                ->where('POID', $poId)
                ->whereNull('DeletedOn')
                ->groupBy('ItemNo')
                ->select('ItemNo', DB::raw('SUM(ReceivedQTY) as TotalReceived'))
                ->pluck('TotalReceived', 'ItemNo');

            $poDetails = [
                'order_no' => $order->OrderNo,
                'supplier' => [
                    'id' => $order->AccountID,
                    'name' => $order->SupplierName ?? $order->SupplierFullName ?? 'Unknown Supplier',
                ],
                'lines' => [],
            ];

            foreach ($orderLines as $line) {
                $receivedQty = $receivedQuantities[$line->iStockCodeID] ?? 0;
                $remainingQty = $line->fQuantity - $receivedQty;

                if ($remainingQty > 0) {
                    // Determine Item Type using helper or direct mapping
                    // Since we fetched ItemTypeName, we can try to map it, or use the helper if we had an object
                    // But here we have stdClass. Let's map manually or create a temporary object if needed.
                    // Actually, the previous implementation used determineItemType with an Item model.
                    // We'll mimic the mapping logic here for performance.

                    $itemTypeStr = $line->ItemTypeName ?? 'Stock';
                    $itemType = EnhancedGoodsReceipt::ITEM_TYPE_STOCK; // Default

                    $typeMapping = [
                        'Stock' => EnhancedGoodsReceipt::ITEM_TYPE_STOCK,
                        'Inventory' => EnhancedGoodsReceipt::ITEM_TYPE_STOCK,
                        'Asset' => EnhancedGoodsReceipt::ITEM_TYPE_ASSET,
                        'Fixed Asset' => EnhancedGoodsReceipt::ITEM_TYPE_ASSET,
                        'Service' => EnhancedGoodsReceipt::ITEM_TYPE_SERVICE,
                        'Non-Stock' => EnhancedGoodsReceipt::ITEM_TYPE_SERVICE,
                    ];

                    if (isset($typeMapping[$itemTypeStr])) {
                        $itemType = $typeMapping[$itemTypeStr];
                    }

                    $poDetails['lines'][] = [
                        'id' => $line->Id,
                        'item_id' => $line->iStockCodeID,
                        'item_code' => $line->ItemCode,
                        'item_name' => $line->ItemName ?? 'Unknown Item',
                        'item_description' => $line->ItemDescription ?? '',
                        'category_code' => $line->CategoryCode,
                        'item_type' => $itemType,
                        'item_type_display' => $this->getItemTypeDisplay($itemType),
                        'ordered_qty' => (float)$line->fQuantity,
                        'received_qty' => (float)$receivedQty,
                        'remaining_qty' => max(0, (float)$remainingQty),
                        'unit_price' => (float)$line->fUnitPriceExcl,
                        'uom' => $line->UOMName ?? 'Each',
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
                    'BatchNumber' => $itemData['batch_number'] ?? null,
                    'ExpiryDate' => $itemData['expiry_date'] ?? null,
                    'ManufactureDate' => $itemData['manufacture_date'] ?? null,
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
     * Get line details for AJAX modal
     */
    public function getLineDetails($lineId)
    {
        $line = EnhancedGoodsReceipt::with(['item', 'item.uom', 'order', 'supplier', 'receiver'])->findOrFail($lineId);

        // Calculate TotalValue if missing
        $totalValue = $line->TotalValue;
        if (($totalValue == 0 || $totalValue == 0.00) && $line->ReceivedQTY > 0) {
            $totalValue = $line->ReceivedQTY * $line->UnitPrice;
        }

        return view('procurement.goods-receipt.partials.line-details', compact('line', 'totalValue'));
    }

    /**
     * Retry processing for a specific line
     */
    public function retryProcessing($lineId): JsonResponse
    {
        try {
            $line = EnhancedGoodsReceipt::findOrFail($lineId);

            // Reset status to pending to allow reprocessing
            $line->update([
                'ProcessingStatus' => EnhancedGoodsReceipt::STATUS_PENDING,
                'ProcessingErrors' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Line item reset for processing.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retry processing', ['line_id' => $lineId, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset line item: ' . $e->getMessage(),
            ], 500);
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
            'supplier.thirdParty.thirdParty',
            'order',
            'orderLine',
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
            'supplier_name' => $grnLines->first()->supplier->thirdParty->thirdParty->TradingName ?? 'N/A',
            'received_date' => $grnLines->first()->ReceivedDate,
            'received_date' => $grnLines->first()->ReceivedDate,
            'total_lines' => $grnLines->count(),
            'total_value' => $grnLines->sum(function ($line) {
                return ($line->TotalValue > 0) ? $line->TotalValue : ($line->ReceivedQTY * $line->UnitPrice);
            }),
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
                if (! empty($validationErrors)) {
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
        $recentGRNs = EnhancedGoodsReceipt::with(['item', 'supplier', 'orderLine'])
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
        if (! $item) {
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

    /**
     * Return an HTML snippet for the GRN Summary modal (AJAX)
     */
    public function showSummary(string $grnId, string $poId)
    {
        $lines = EnhancedGoodsReceipt::with(['item', 'receiver', 'supplier'])
            ->where('GRNID', $grnId)
            ->where('POID', $poId)
            ->get();

        if ($lines->isEmpty()) {
            return response()->json(['error' => 'GRN not found'], 404);
        }

        $first = $lines->first();
        $totalValue = $lines->sum(fn ($l) => $l->TotalValue > 0 ? $l->TotalValue : ($l->ReceivedQTY * $l->UnitPrice));
        $poNumber = optional($first->order)->OrderNo ?? $poId;
        $supplier = $first->supplier?->thirdParty?->thirdParty?->TradingName
            ?? $first->supplier?->thirdParty?->thirdParty?->ThirdPartyName
            ?? 'N/A';
        $receivedDate = $first->ReceivedDate
            ? \Carbon\Carbon::parse($first->ReceivedDate)->format('d M Y')
            : '—';
        $statusLabel = $first->InspectionStatus?->label() ?? ucfirst($first->InspectionStatus ?? 'draft');
        $statusColor = $first->InspectionStatus?->badgeColor() ?? 'warning';

        $html = "
        <div class='mb-3'>
            <div class='row g-2'>
                <div class='col-md-6'>
                    <table class='table table-sm table-borderless mb-0'>
                        <tr><th class='text-muted' style='width:40%'>GRN ID</th><td><strong>{$grnId}</strong></td></tr>
                        <tr><th class='text-muted'>PO Number</th><td>{$poNumber}</td></tr>
                        <tr><th class='text-muted'>Supplier</th><td>{$supplier}</td></tr>
                    </table>
                </div>
                <div class='col-md-6'>
                    <table class='table table-sm table-borderless mb-0'>
                        <tr><th class='text-muted' style='width:40%'>Received</th><td>{$receivedDate}</td></tr>
                        <tr><th class='text-muted'>Status</th><td><span class='badge bg-{$statusColor}'>{$statusLabel}</span></td></tr>
                        <tr><th class='text-muted'>Total Value</th><td><strong>KES " . number_format($totalValue, 2) . "</strong></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <hr>
        <h6 class='mb-2'>Line Items ({$lines->count()})</h6>
        <div class='table-responsive'>
            <table class='table table-sm table-hover'>
                <thead class='table-light'>
                    <tr>
                        <th>Item</th>
                        <th class='text-center'>Ordered Qty</th>
                        <th class='text-center'>Received Qty</th>
                        <th class='text-end'>Unit Price</th>
                        <th class='text-end'>Total</th>
                        <th>Processing</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($lines as $line) {
            $itemName = $line->item?->ItemName ?? $line->ItemNo ?? 'N/A';
            $lineTotal = $line->TotalValue > 0 ? $line->TotalValue : ($line->ReceivedQTY * $line->UnitPrice);
            $procBadge = match ($line->ProcessingStatus ?? '') {
                'processed' => "<span class='badge bg-success'>Processed</span>",
                'error' => "<span class='badge bg-danger'>Error</span>",
                default => "<span class='badge bg-secondary'>Pending</span>",
            };

            $html .= "
                    <tr>
                        <td>{$itemName}</td>
                        <td class='text-center'>" . ($line->OrderedQTY ?? $line->POQTY ?? '—') . "</td>
                        <td class='text-center'>{$line->ReceivedQTY}</td>
                        <td class='text-end'>KES " . number_format($line->UnitPrice, 2) . "</td>
                        <td class='text-end'>KES " . number_format($lineTotal, 2) . "</td>
                        <td>{$procBadge}</td>
                    </tr>";
        }

        $html .= "
                </tbody>
                <tfoot class='table-light'>
                    <tr>
                        <th colspan='4' class='text-end'>Grand Total</th>
                        <th class='text-end'>KES " . number_format($totalValue, 2) . "</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>";

        return response($html);
    }
}
