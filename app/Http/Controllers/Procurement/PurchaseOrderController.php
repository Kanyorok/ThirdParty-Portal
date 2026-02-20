<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\ApprovalEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\PurchaseOrderRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Procurement\Order;
use App\Services\Core\DocumentApprovalService;
use App\Services\Procurement\Items\ItemService;
use App\Services\Procurement\Orders\OrderService;
use App\Services\Procurement\Orders\OrderSourceService;
use App\Services\Procurement\RFQ\RFQService;
use App\Services\ThirdParties\SupplierService;
use App\Services\Workflow\ApprovalWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    /**
     * How long (seconds) to cache a successful store response for idempotency replay.
     */
    private const IDEMPOTENCY_TTL = 86400; // 24 hours

    /**
     * How long (seconds) to hold the processing lock.
     * Must exceed the slowest expected execution of store() — header + lines + totals + workflow.
     */
    private const IDEMPOTENCY_LOCK_TTL = 60;

    public function __construct(
        protected ItemService $itemService,
        protected OrderService $orderService,
        protected DocumentApprovalService $documentApprovalService,
        protected RFQService $rfqService,
        protected ApprovalWorkflow $workflowService,
        protected OrderSourceService $orderSourceService
    ) {
        $this->middleware('ajax')->except([
            'index',
            'create',
            'store',
            'show',
            'linkRFQ',
            'fetchRFQDetails',
            'approval',
            'approve',
            'getItemDetails',
            'getSuppliers',
            'getSupplierDetails',
            'prequalifiedSuppliersByCategory',
            'getPrequalifiedSuppliers',
            'getRFQItems',
            'getAwardedRFQs',
            'getAwardedTenders',
            'getTenderItems',
            'getContractItems',
            'getDirectPlans',
            'getDirectPlanItems',
            'getPlanItemCategories',
            'getPlanItemsByCategory',
            'getRootItemCategories',
            'getItemsByCategoryWithDescendants',
            'getDirectPlanCategories',
            'getDirectPlanItemsByCategory',
        ]);
    }

    /**
     * Display a listing of purchase orders.
     */
    public function index()
    {
        $this->authorize('viewAny', Order::class);

        try {
            $perPage = (int) request()->query('perPage', 20);
            $perPage = $perPage > 0 ? $perPage : 20;
            $details = $this->orderService->fetchOrdersPaginated($perPage);

            Log::info('PurchaseOrderController@index paginator', [
                'perPage' => $perPage,
                'total'   => $details->total(),
            ]);

            return view('procurement.orders.index', compact('details'));
        } catch (\Exception $e) {
            Log::error('Failed to fetch orders: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to fetch orders.');
        }
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create()
    {
        $this->authorize('create', Order::class);

        try {
            $itemTypes = $this->itemService->getTypes();

            $allItems = DB::table('t_Items as i')
                ->leftJoin('t_ItemTypes as it', 'i.ItemType', '=', 'it.Id')
                ->leftJoin('t_ItemCategories as ic', 'i.Category', '=', 'ic.Id')
                ->whereNull('i.DeletedBy')
                ->select(
                    'i.Id as itemCode',
                    'i.ItemName as itemName',
                    'i.ItemDescription as description',
                    'i.ItemPrice as unitPrice',
                    'it.TypeName as itemType',
                    'ic.Name as categoryName'
                )
                ->orderBy('i.ItemName')
                ->get();

            $rfqResponses = $this->rfqService->fetchRFQ();
            $uniqueRfqs   = collect($rfqResponses)->unique('RFQNumber')->values();
            $suppliers    = SupplierService::getSuppliers();

            $paymentTerms = $this->fetchPaymentTerms();

            $taxRules = DB::table('t_FinanceTaxRuleConfiguration as tr')
                ->join('t_FinanceTaxType as tt', 'tr.TaxTypeId', '=', 'tt.Id')
                ->whereNull('tr.DeletedOn')
                ->where('tr.Status', 1)
                ->select('tr.Id', 'tt.TaxTypeName', 'tr.Rate')
                ->get();

            $awardedRfqs    = $this->orderSourceService->getAwardedRFQs();
            $awardedTenders = $this->orderSourceService->getAwardedTenders();
            $contracts      = $this->orderSourceService->getContracts();

            // Contract prefill support
            $prefillContract = null;
            $contractId      = request('contractId');

            if (! empty($contractId)) {
                try {
                    $contractRow = DB::table('t_TenderAwards as ta')
                        ->leftJoin('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
                        ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
                        ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                        ->select(
                            'ta.Id as ContractId',
                            'ta.ContractRef',
                            'ta.WinningSupplierID as SupplierId',
                            DB::raw('tp.Id as ThirdPartyId'),
                            DB::raw('tp.TradingName as SupplierName'),
                            DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                        )
                        ->where('ta.Id', (int) $contractId)
                        ->first();

                    if ($contractRow && ! empty($contractRow->ContractRef)) {
                        $uniqueRfqs = collect($uniqueRfqs);
                        $exists     = $uniqueRfqs->contains(fn ($r) => ($r->RFQNumber ?? null) === ($contractRow->ContractRef ?? null));

                        if (! $exists) {
                            $uniqueRfqs = $uniqueRfqs->prepend((object) ['RFQNumber' => $contractRow->ContractRef]);
                        }

                        $rfqResponsesCol = collect($rfqResponses ?? []);
                        $rfqResponsesCol = $rfqResponsesCol->prepend((object) [
                            'RFQNumber'    => $contractRow->ContractRef,
                            'SupplierId'   => (int) ($contractRow->ThirdPartyId ?? 0),
                            'SupplierID'   => (int) ($contractRow->ThirdPartyId ?? 0),
                            'SupplierName' => $contractRow->SupplierName ?? '',
                            'Address'      => $contractRow->Address ?? '',
                        ]);
                        $rfqResponses = $rfqResponsesCol->values();

                        $prefillContract = [
                            'ref'          => $contractRow->ContractRef,
                            'supplierId'   => (int) ($contractRow->SupplierId ?? 0),
                            'thirdPartyId' => (int) ($contractRow->ThirdPartyId ?? 0),
                            'supplierName' => $contractRow->SupplierName ?? '',
                            'address'      => $contractRow->Address ?? '',
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::warning('Contract prefill failed', ['error' => $e->getMessage()]);
                }
            }

            $sourceType = $prefillContract ? 'CONTRACT' : 'RFQ';

            return view('procurement.orders.create', [
                'itemTypes'            => $itemTypes ?? [],
                'allItems'             => $allItems ?? collect(),
                'rfqs'                 => $uniqueRfqs ?? [],
                'rfqResponses'         => $rfqResponses ?? [],
                'suppliers'            => $suppliers ?? [],
                'paymentTerms'         => $paymentTerms ?? [],
                'prefillContract'      => $prefillContract,
                'sourceType'           => $sourceType,
                'contracts'            => $contracts ?? collect(),
                'awardedRfqs'          => $awardedRfqs ?? collect(),
                'convertedRFQIds'      => [],
                'awardedTenders'       => $awardedTenders ?? collect(),
                'convertedTenderIds'   => [],
                'usedReferenceNumbers' => [],
                'taxRules'             => $taxRules ?? collect(),
            ]);
        } catch (\Exception $e) {
            Log::error('Data fetch failed: ' . $e->getMessage());

            return view('procurement.orders.create', [
                'suppliers'            => [],
                'itemTypes'            => [],
                'allItems'             => collect(),
                'rfqs'                 => [],
                'rfqResponses'         => [],
                'paymentTerms'         => $this->fetchPaymentTerms(),
                'awardedRfqs'          => [],
                'convertedRFQIds'      => [],
                'contracts'            => collect(),
                'sourceType'           => 'RFQ',
                'prefillContract'      => null,
                'usedReferenceNumbers' => [],
                'taxRules'             => collect(),
            ])->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created Purchase Order.
     *
     * Idempotency strategy
     * ─────────────────────────────────────────────────────────────────────────
     * This method is the most expensive in the controller — it creates a PO
     * header, inserts N line items, recalculates totals, updates source info,
     * and submits an approval workflow. A double-submit (back button, network
     * retry, impatient double-click) would produce a duplicate PO and duplicate
     * workflow entries, so idempotency is critical here.
     *
     * 1. Client sends a unique `Idempotency-Key` header per *intended* request
     *    (e.g. a UUID generated in JS before the form submit). Falls back to a
     *    payload hash when no header is present.
     *
     * 2. First call   → acquire lock → run → cache PO id + order number →
     *                   release lock → redirect to show page.
     *
     * 3. Duplicate    → cached result found → redirect to the same PO without
     *    (success)       touching the DB.
     *
     * 4. In-flight    → lock is held → redirect back with a "please wait" error
     *                   so the user knows not to submit again.
     *
     * 5. Failed call  → result is NOT cached → retry is treated as a fresh
     *                   attempt.
     *
     * The lock TTL is set to 60 s because this operation is heavier than a
     * simple INSERT — it iterates over line items and calls the workflow service.
     */
    public function store(PurchaseOrderRequest $request)
    {
        $this->authorize('create', Order::class);

        try {
            $validated = $request->validated();
            $actor     = auth()->user();

            // ------------------------------------------------------------------
            // 1. Build the idempotency key.
            //    Prefer an explicit client-supplied header so two genuinely
            //    *different* POs with identical fields never collide.
            // ------------------------------------------------------------------
            $clientKey = $request->header('Idempotency-Key');

            $idempotencyKey = $clientKey
                ? 'po_idem:' . $actor->id . ':' . $clientKey
                : 'po_idem:' . $actor->id . ':' . md5(json_encode($validated));

            $resultCacheKey = $idempotencyKey . ':result';
            $lockKey        = $idempotencyKey . ':lock';

            // ------------------------------------------------------------------
            // 2. Replay a previously cached success response (step 3 above).
            //    The user is sent to the already-created PO without any DB work.
            // ------------------------------------------------------------------
            $cached = Cache::get($resultCacheKey);

            if ($cached !== null) {
                Log::info('Idempotent replay for PO store.', [
                    'user_id'         => $actor->id,
                    'idempotency_key' => $idempotencyKey,
                    'po_id'           => $cached['po_id'],
                ]);

                return redirect()
                    ->route('purchaseOrder.show', $cached['po_id'])
                    ->with('success', "Purchase Order already created (duplicate request ignored). LPO Number: {$cached['order_no']}");
            }

            // ------------------------------------------------------------------
            // 3. Acquire a short-lived atomic lock (step 4 above).
            // ------------------------------------------------------------------
            $lock = Cache::lock($lockKey, self::IDEMPOTENCY_LOCK_TTL);

            if (! $lock->get()) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Your previous request is still being processed. Please wait a moment and try again.');
            }

            try {
                // Re-check the cache inside the lock (double-checked locking).
                $cached = Cache::get($resultCacheKey);

                if ($cached !== null) {
                    return redirect()
                        ->route('purchaseOrder.show', $cached['po_id'])
                        ->with('success', "Purchase Order already created (duplicate request ignored). LPO Number: {$cached['order_no']}");
                }

                // --------------------------------------------------------------
                // 4. Validate quantities against the source document.
                // --------------------------------------------------------------
                if ($request->has('SourceType') && $request->has('SourceId')) {
                    $sourceType = $request->input('SourceType');
                    $sourceId   = $request->input('SourceId');

                    $availableItems = collect();

                    try {
                        if ($sourceType === 'CONTRACT') {
                            $availableItems = $this->orderSourceService->getContractItems($sourceId, 'tender');
                        } elseif ($sourceType === 'CONTRACT-RFQ') {
                            $availableItems = $this->orderSourceService->getContractItems($sourceId, 'rfq');
                        } elseif ($sourceType === 'RFQ') {
                            $availableItems = $this->orderSourceService->getRFQItems($sourceId);
                        } elseif ($sourceType === 'TENDER') {
                            $availableItems = $this->orderSourceService->getTenderItems($sourceId);
                        } elseif ($sourceType === 'PLAN') {
                            $availableItems = $this->orderSourceService->getDirectPlanItems($sourceId);
                        }

                        if ($availableItems->isNotEmpty()) {
                            $availableMap = $availableItems->pluck('quantity', 'itemCode')->toArray();
                            $itemNameMap  = $availableItems->pluck('itemName', 'itemCode')->toArray();

                            foreach ($validated['itemCode'] as $idx => $code) {
                                $qty = $validated['quantity'][$idx];
                                if (isset($availableMap[$code]) && $qty > $availableMap[$code]) {
                                    $name      = $itemNameMap[$code] ?? $code;
                                    $remaining = $availableMap[$code];

                                    // Release the lock before redirecting back — the
                                    // operation hasn't started so a retry should be free.
                                    $lock->release();

                                    return back()->withInput()->with(
                                        'error',
                                        "Quantity for item '{$name}' exceeds remaining quantity. Available: {$remaining}, Requested: {$qty}"
                                    );
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Source quantity validation failed, proceeding anyway.', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // --------------------------------------------------------------
                // 5. Create the PO header.
                // --------------------------------------------------------------
                $taxes      = $validated['tax'] ?? [];
                $firstTaxId = $taxes[0] ?? null;
                if ($firstTaxId === '' || $firstTaxId === '0' || $firstTaxId === 0) {
                    $firstTaxId = null;
                }

                $poResult = $this->orderService->addPO(
                    $validated['supplier'],
                    $validated['Date'],
                    $validated['refNo'] ?? null,
                    $validated['priority'] ?? null,
                    $validated['terms'],
                    $actor,
                    $firstTaxId
                );

                if ($poResult['status'] !== 'success') {
                    return back()
                        ->withInput()
                        ->with('error', $poResult['message'] ?? 'Failed to create Purchase Order');
                }

                $poId = $poResult['po_id'];

                // --------------------------------------------------------------
                // 6. Add PO line items.
                // --------------------------------------------------------------
                $itemCodes  = $validated['itemCode'];
                $quantities = $validated['quantity'];
                $unitPrices = $validated['unitPrice'];
                $taxes      = $validated['tax'] ?? [];
                $discounts  = $validated['discount'] ?? [];
                $lineTotals = $validated['lineTotal'];

                foreach ($itemCodes as $index => $itemCode) {
                    $taxId = $taxes[$index] ?? null;
                    if ($taxId === '' || $taxId === '0' || $taxId === 0) {
                        $taxId = null;
                    }

                    $lineResult = $this->orderService->addPOLines(
                        $itemCode,
                        $quantities[$index],
                        $unitPrices[$index],
                        $taxId,
                        $discounts[$index] ?? 0,
                        $lineTotals[$index],
                        $actor,
                        $poId
                    );

                    if ($lineResult['status'] !== 'success') {
                        Log::warning('Failed to add PO line item', [
                            'po_id'     => $poId,
                            'item_code' => $itemCode,
                            'error'     => $lineResult['message'] ?? 'Unknown error',
                        ]);
                    }
                }

                // --------------------------------------------------------------
                // 7. Calculate PO totals.
                // --------------------------------------------------------------
                $this->orderService->AddPurchaseOrderSum($poId);

                // --------------------------------------------------------------
                // 8. Record source document reference.
                // --------------------------------------------------------------
                if ($request->has('SourceType') && $request->has('SourceId')) {
                    Order::where('Id', $poId)->update([
                        'SourceType' => $request->input('SourceType'),
                        'SourceId'   => $request->input('SourceId'),
                    ]);
                }

                // --------------------------------------------------------------
                // 9. Submit approval workflow (non-fatal if it fails).
                // --------------------------------------------------------------
                try {
                    $order = Order::findOrFail($poId);
                    $this->workflowService->submit(
                        $order,
                        $actor,
                        ApprovalEnum::Submitted,
                        'Purchase Order created and submitted for approval'
                    );
                    Log::info('Approval workflow submitted for PO', ['po_id' => $poId]);
                } catch (\Exception $e) {
                    Log::warning('Failed to submit approval workflow for PO', [
                        'po_id' => $poId,
                        'error' => $e->getMessage(),
                    ]);
                    // Non-fatal: the PO exists; the approver can re-submit manually.
                }

                // --------------------------------------------------------------
                // 10. Cache the success result so retries replay it (step 3).
                //     Failures are intentionally NOT cached so they can be retried.
                // --------------------------------------------------------------
                $orderNo = $poResult['order_no'] ?? '';

                Cache::put($resultCacheKey, [
                    'po_id'    => $poId,
                    'order_no' => $orderNo,
                ], self::IDEMPOTENCY_TTL);

                Log::info('Purchase Order created successfully.', [
                    'po_id'    => $poId,
                    'order_no' => $orderNo,
                    'user_id'  => $actor->id,
                ]);

                return redirect()
                    ->route('purchaseOrder.show', $poId)
                    ->with('success', "Purchase Order created successfully. LPO Number: {$orderNo}");

            } catch (\Exception $e) {
                // Do NOT cache — the user should be able to retry.
                Log::error('Error creating Purchase Order', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return back()
                    ->withInput()
                    ->with('error', 'An error occurred while creating the Purchase Order: ' . $e->getMessage());

            } finally {
                // Always release the lock — even on exception.
                // Without this it would be held for IDEMPOTENCY_LOCK_TTL seconds,
                // blocking every retry for that entire window.
                $lock->release();
            }

        } catch (\Exception $e) {
            Log::error('Unexpected error in PO store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Display the specified purchase order.
     */
    public function show(Request $request, string $id)
    {
        try {
            Log::info('Loading PO show page', ['order_id' => $id]);

            $order = Order::findOrFail($id);
            $this->authorize('view', $order);

            $orderInfo = $this->orderService->fetchOrderDetails($id);
            $lineInfo  = $this->orderService->fetchOrderLineDetails($id);

            Log::info('Order details and line items fetched', [
                'order_id'   => $id,
                'line_count' => count($lineInfo),
            ]);

            // Fetch workflow history
            $history = collect();

            try {
                $workflowData = $this->workflowService->getWorkflowStatus($order->getMorphClass(), $order->getKey());
                $historyArr   = $workflowData['completedApprovals'] ?? [];
                $stageName    = $workflowData['currentStage']['name'] ?? 'Stage';

                $fetchedHistory = collect($historyArr)->map(function ($item) use ($stageName) {
                    $obj = (object) $item;
                    $obj->StatusId = $obj->StatusId ?? 'A';
                    $obj->stage    = $obj->stage ?? (object) ['StageName' => $stageName];
                    $obj->status   = $obj->status ?? (object) ['Description' => 'Approved'];
                    $obj->creator  = $obj->creator ?? (object) ['Name' => $item->Name ?? 'Unknown'];

                    return $obj;
                });

                $history = $history->merge($fetchedHistory);
                Log::info('Workflow history fetched', ['order_id' => $id, 'history_count' => $history->count()]);
            } catch (\Exception $e) {
                Log::warning('Failed to fetch workflow history', [
                    'order_id' => $id,
                    'error'    => $e->getMessage(),
                ]);
            }

            // Append a "Submitted" entry so initiation is always visible (oldest, so pushed last).
            $history->push((object) [
                'stage'     => (object) ['StageName' => 'Initiation'],
                'status'    => (object) ['Description' => 'Submitted'],
                'creator'   => (object) ['Name' => $order->creator->Name ?? 'Unknown'],
                'CreatedOn' => $order->CreatedOn,
                'Notes'     => 'Order initiated',
                'StatusId'  => '',
            ]);

            // Check if the current user can approve
            try {
                $canApprove = $this->workflowService->canApproveModel($order, auth()->user());
                Log::info('Can-approve check completed', ['order_id' => $id, 'can_approve' => $canApprove]);
            } catch (\Exception $e) {
                Log::warning('Failed to check approval permission', [
                    'order_id' => $id,
                    'error'    => $e->getMessage(),
                ]);
                $canApprove = false;
            }

            // Get workflow status
            try {
                $workflowStatus  = $this->workflowService->getWorkflowStatus($order->getMorphClass(), $order->getKey());
                $isFullyApproved = ($workflowStatus['totalPending'] ?? 0) === 0;
                Log::info('Workflow status fetched', ['order_id' => $id, 'status' => $workflowStatus]);
            } catch (\Exception $e) {
                Log::warning('Failed to get workflow status, using fallback', [
                    'order_id' => $id,
                    'error'    => $e->getMessage(),
                ]);

                $pendingApprovals = DB::table('t_WorkFlowPending as p')
                    ->join('t_WorkFlowStages as s', 'p.StageId', '=', 's.Id')
                    ->leftJoin('t_Users as u', 'p.UserId', '=', 'u.Id')
                    ->leftJoin('t_WorkFlowGroups as g', 'p.GroupId', '=', 'g.Id')
                    ->where('p.Source', $order->getTable())
                    ->where('p.SourceID', $order->Id)
                    ->select('s.StageName as stage', DB::raw("COALESCE(u.Name, g.Name, 'Unknown') as approver"))
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->toArray();

                $workflowStatus  = ['pendingApprovals' => $pendingApprovals];
                $isFullyApproved = count($pendingApprovals) === 0;
            }

            Log::info('Rendering PO show view', ['order_id' => $id]);

            if ($request->ajax()) {
                return view(
                    'procurement.orders.partials.show_content',
                    compact('orderInfo', 'lineInfo', 'history', 'canApprove', 'isFullyApproved', 'workflowStatus')
                )->render();
            }

            return view(
                'procurement.orders.show',
                compact('orderInfo', 'lineInfo', 'history', 'canApprove', 'isFullyApproved', 'workflowStatus')
            );

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized access to Order ID: {$id}", ['user_id' => auth()->id()]);

            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Order ID {$id} not found.");

            return redirect()->back()->with('error', 'Order not found.');
        }
    }

    /**
     * Show the form for editing the specified purchase order
     */
    public function edit($id)
    {
        try {
            $order = $this->orderService->getOrder($id);

            if (! $order) {
                return redirect()->route('purchaseOrder.index')
                    ->with('error', 'Order not found.');
            }

            $this->authorize('update', $order);

            $suppliers   = SupplierService::getSuppliers();
            $orderInfo   = $this->orderService->fetchOrderDetails($id);
            $lineInfo    = $this->orderService->fetchOrderLineDetails($id);
            $paymentTerms = $this->fetchPaymentTerms();

            return view(
                'procurement.orders.edit',
                compact('order', 'orderInfo', 'lineInfo', 'suppliers', 'paymentTerms')
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized access to edit Order ID: {$id}", [
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Exception $e) {
            Log::error("Failed to load edit page for Order ID {$id}", [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load order edit page.');
        }
    }

    /**
     * Update the specified purchase order in storage
     */
    public function update(PurchaseOrderRequest $request, $id)
    {
        try {
            $order = $this->orderService->getOrder($id);

            if (! $order) {
                return redirect()->route('purchaseOrder.index')
                    ->with('error', 'Order not found.');
            }

            $this->authorize('update', $order);

            $updated = $this->orderService->updateOrder($id, $request->validated());

            if (! $updated) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to update order. Please try again.');
            }

            Log::info("Order ID {$id} updated successfully by user " . auth()->id());

            return redirect()->route('purchaseOrder.show', $id)
                ->with('success', 'Order updated successfully.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized update attempt on Order ID: {$id}", [
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Exception $e) {
            Log::error("Failed to update Order ID {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred while updating the order.');
        }
    }

    /**
     * Remove the specified purchase order from storage
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('delete', $order);

            $deleted = $this->orderService->deleteOrder($id);

            if (! $deleted) {
                return redirect()->back()->with('error', 'Failed to delete order. Please try again.');
            }

            Log::info("Order ID {$id} deleted by user " . auth()->id());

            return redirect()->route('purchaseOrder.index')->with('success', 'Order deleted successfully.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized delete attempt on Order ID: {$id}", [
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Order ID {$id} not found for deletion.");

            return redirect()->route('purchaseOrder.index')->with('error', 'Order not found.');
        } catch (\Exception $e) {
            Log::error("Failed to delete order ID {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to delete order.');
        }
    }

    /**
     * Show the approval page.
     */
    public function approval($id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('view', $order);

            $orderInfo = $this->orderService->fetchOrderDetails($id);
            $lineInfo  = $this->orderService->fetchOrderLineDetails($id);

            return view('procurement.orders.approval', compact('orderInfo', 'lineInfo'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized access to Order approval ID: {$id}", ['user_id' => auth()->id()]);

            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Exception $e) {
            Log::error("Failed to load approval page for Order ID {$id}", ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to load approval page.');
        }
    }

    /**
     * Submit an order for approval.
     */
    public function submit(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('update', $order);

            $result = $this->workflowService->submit(
                $order,
                auth()->user(),
                ApprovalEnum::Submitted,
                $request->input('remarks', 'Submitted for approval')
            );

            if ($result) {
                return redirect()->back()->with('success', 'Order submitted for approval successfully.');
            }

            return redirect()->back()->with('error', 'Failed to submit order for approval.');
        } catch (\Exception $e) {
            Log::error('Order submission failed', ['order_id' => $id, 'error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to submit order: ' . $e->getMessage());
        }
    }

    /**
     * Approve an order.
     */
    public function approve(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('approve', $order);

            if ($request->input('action') === 'reject') {
                return $this->reject($request, $id);
            }

            // Maker-Checker Rule: prevent self-approval
            if ($order->CreatedBy == auth()->id()) {
                Log::warning('Maker-checker violation: User attempted to approve own PO', [
                    'order_id' => $id,
                    'user_id'  => auth()->id(),
                ]);

                return redirect()->back()->with('error', 'You cannot approve your own Purchase Order (Maker-Checker rule).');
            }

            $result = $this->workflowService->approve(
                $order,
                auth()->user(),
                ApprovalEnum::Approved,
                $request->input('remarks', 'Approved'),
                'DocStatus'
            );

            if ($result) {
                return redirect()->back()->with('success', 'Order approved successfully.');
            }

            return redirect()->back()->with('error', 'Failed to approve order.');
        } catch (\Exception $e) {
            Log::error('Order approval failed', ['order_id' => $id, 'error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to approve order: ' . $e->getMessage());
        }
    }

    /**
     * Reject an order.
     */
    public function reject(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('approve', $order);

            $result = $this->workflowService->reject(
                $order,
                auth()->user(),
                ApprovalEnum::Rejected,
                $request->input('remarks', 'Rejected'),
                'DocStatus'
            );

            if ($result) {
                return redirect()->back()->with('success', 'Order rejected successfully.');
            }

            return redirect()->back()->with('error', 'Failed to reject order.');
        } catch (\Exception $e) {
            Log::error('Order rejection failed', ['order_id' => $id, 'error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to reject order: ' . $e->getMessage());
        }
    }

    /**
     * Return an order for modification.
     */
    public function return(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('approve', $order);

            $result = $this->workflowService->approve(
                $order,
                auth()->user(),
                ApprovalEnum::Pending,
                $request->input('remarks', 'Returned for modification'),
                'DocStatus'
            );

            if ($result) {
                return redirect()->back()->with('success', 'Order returned for modification successfully.');
            }

            return redirect()->back()->with('error', 'Failed to return order.');
        } catch (\Exception $e) {
            Log::error('Order return failed', ['order_id' => $id, 'error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to return order: ' . $e->getMessage());
        }
    }

    /**
     * Add a comment to the order workflow.
     */
    public function comment(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('view', $order);

            return redirect()->back()->with('info', 'Comment feature not yet implemented.');
        } catch (\Exception $e) {
            Log::error('Failed to add comment', ['order_id' => $id, 'error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to add comment: ' . $e->getMessage());
        }
    }

   
    // JSON endpoints
  
    public function getItemDetails($item): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->itemService->getItemDetails($item),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getSuppliers(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => SupplierService::getSuppliers(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers.',
            ], 500);
        }
    }

    public function getSupplierDetails($supplier): JsonResponse
    {
        try {
            $details = SupplierService::getSupplierDetails($supplier);
            $address = '';

            if ($details) {
                if (is_array($details) && isset($details['Address'])) {
                    $address = $details['Address'];
                } elseif (is_object($details) && isset($details->Address)) {
                    $address = $details->Address;
                }
            }

            return response()->json([
                'success' => true,
                'data'    => ['Address' => $address],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch supplier details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function linkRFQ()
    {
        $this->authorize('viewAny', Order::class);

        try {
            $RFQ = $this->rfqService->fetchRFQ();
        } catch (\Exception $e) {
            Log::error('Error fetching RFQs: ' . $e->getMessage());
            $RFQ = collect();
        }

        return view('procurement.orders.rfqlink', compact('RFQ'));
    }

    public function fetchRFQDetails($id): JsonResponse
    {
        $this->authorize('create', Order::class);

        try {
            return response()->json([
                'success' => true,
                'data'    => $this->rfqService->RFQTOPO($id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RFQ.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getAwardedRFQs()
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->orderSourceService->getAwardedRFQs(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch awarded RFQs: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch awarded RFQs',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getAwardedTenders()
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->orderSourceService->getAwardedTenders(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch awarded Tenders: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch awarded Tenders',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getTenderItems($tenderId)
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->orderSourceService->getTenderItems($tenderId),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Tender Items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getContractItems($contractId)
    {
        try {
            $type  = request('type', 'tender');
            $items = $this->orderSourceService->getContractItems($contractId, $type);

            return response()->json([
                'success' => true,
                'data'    => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Contract Items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Contract Items',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getDirectPlans(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->orderSourceService->getDirectPlans(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch direct plans: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch direct plans',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getRootItemCategories(): JsonResponse
    {
        try {
            $categories = DB::table('t_ItemCategories')
                ->where('ParentID', 0)
                ->orWhereNull('ParentID')
                ->select('Id', 'Name')
                ->get();

            return response()->json(['success' => true, 'data' => $categories]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'data' => []]);
        }
    }

    public function getDirectPlanCategories(): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => []]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'data' => []]);
        }
    }

    public function getPrequalifiedSuppliers($categoryId): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => []]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'data' => []]);
        }
    }

    public function relatedPO()
    {
        return view('procurement.orders.index');
    }

    public function getRFQItems($rfqId)
    {
        try {
            $supplierId = request('supplierId');
            $items      = $this->orderSourceService->getRFQItems($rfqId, $supplierId);

            return response()->json([
                'success' => true,
                'data'    => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get RFQ items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RFQ items',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getDirectPlanItems($planId)
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->orderSourceService->getDirectPlanItems($planId),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Direct Plan Items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Direct Plan Items',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getPlanItemCategories($planId): JsonResponse
    {
        try {
            $directMethod = DB::table('t_CodeDetails')
                ->where('CodeID', 'ProcurementMethod')
                ->where(function ($q) {
                    $q->where('Description', 'LIKE', '%Direct%')
                        ->orWhere('Value', 'LIKE', '%Direct%');
                })
                ->value('ID');

            $categories = DB::table('t_PlanLineItem as pli')
                ->join('t_ItemCategories as ic', 'pli.CategoryID', '=', 'ic.Id')
                ->where('pli.PlanID', $planId)
                ->where('pli.ProcurementMethod', $directMethod)
                ->whereNull('pli.DeletedOn')
                ->select('ic.Id', 'ic.Name')
                ->distinct()
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get plan categories: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function prequalifiedSuppliersByCategory($categoryId): JsonResponse
    {
        try {
            $suppliers = DB::table('t_Suppliers as s')
                ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->join('t_ThirdParties as tp', 'sm.ThirdPartyId', '=', 'tp.Id')
                ->where('s.Active_Status', 1)
                ->whereNull('s.DeletedOn')
                ->groupBy('sm.Id', 'tp.Id', 'tp.TradingName', 'tp.ThirdPartyName', 'tp.PhysicalAddress')
                ->select(
                    DB::raw('MIN(s.Id) as SupplierId'),
                    'sm.Id as SupplierMasterId',
                    'tp.Id as ThirdPartyId',
                    DB::raw("COALESCE(tp.TradingName, tp.ThirdPartyName, '') as SupplierName"),
                    DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                )
                ->orderBy('SupplierName')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $suppliers,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch prequalified suppliers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Fetch payment terms from CodeDetails, with a fallback raw query.
     * Extracted to avoid duplication between create() and its error fallback.
     */
    private function fetchPaymentTerms()
    {
        try {
            $terms = CodeDetail::query()
                ->where('CodeID', 'PaymentTerm')
                ->orderBy('DisplayOrder')
                ->get(['ID', 'Description']);

            if ($terms->isEmpty()) {
                $terms = DB::table('t_CodeDetails')
                    ->whereIn(DB::raw('RTRIM(LTRIM(CodeID))'), ['PaymentTerm', 'PaymentTerms'])
                    ->orderBy('DisplayOrder')
                    ->select('ID', 'Description')
                    ->get();
            }

            return $terms;
        } catch (\Throwable $e) {
            Log::warning('Failed to fetch payment terms: ' . $e->getMessage());

            return collect();
        }
    }
}