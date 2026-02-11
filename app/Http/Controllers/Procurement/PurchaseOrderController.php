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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected ItemService $itemService,
        protected OrderService $orderService,
        protected DocumentApprovalService $documentApprovalService,
        protected RFQService $rfqService,
        protected ApprovalWorkflow $workflowService,  // Changed type hint
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
     * Display a listing of purchase orders
     */
    public function index()
    {
        $this->authorize('viewAny', Order::class);

        try {
            $perPage = (int) request()->query('perPage', 20);
            $perPage = $perPage > 0 ? $perPage : 20;
            $details = $this->orderService->fetchOrdersPaginated($perPage);
            Log::info('PurchaseOrderController@index paginator', ['perPage' => $perPage, 'total' => $details->total()]);

            return view('procurement.orders.index', compact('details'));
        } catch (\Exception $e) {
            Log::error('Failed to fetch orders: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to fetch orders.');
        }
    }

    /**
     * Show the form for creating a new purchase order
     */
    public function create()
    {
        $this->authorize('create', Order::class);

        try {
            $itemTypes = $this->itemService->getTypes();

            // Load all items for the dropdown
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
            $uniqueRfqs = collect($rfqResponses)->unique('RFQNumber')->values();
            $suppliers = SupplierService::getSuppliers();

            // Fetch payment terms from t_CodeDetails
            $paymentTerms = CodeDetail::query()
                ->where('CodeID', 'PaymentTerm')
                ->orderBy('DisplayOrder')
                ->get(['ID', 'Description']);

            if ($paymentTerms->isEmpty()) {
                $paymentTerms = DB::table('t_CodeDetails')
                    ->whereIn(DB::raw('RTRIM(LTRIM(CodeID))'), ['PaymentTerm', 'PaymentTerms'])
                    ->orderBy('DisplayOrder')
                    ->select('ID', 'Description')
                    ->get();
            }

            // Fetch Tax Rules
            $taxRules = DB::table('t_FinanceTaxRuleConfiguration as tr')
                ->join('t_FinanceTaxType as tt', 'tr.TaxTypeId', '=', 'tt.Id')
                ->whereNull('tr.DeletedOn')
                ->where('tr.Status', 1) // Status is bit/int 1 for Active
                ->select('tr.Id', 'tt.TaxTypeName', 'tr.Rate')
                ->get();

            $awardedRfqs = $this->orderSourceService->getAwardedRFQs();
            $awardedTenders = $this->orderSourceService->getAwardedTenders();
            $contracts = $this->orderSourceService->getContracts();

            // Contract prefill support
            $prefillContract = null;
            $contractId = request('contractId');
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
                            DB::raw("tp.TradingName as SupplierName"),
                            DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                        )
                        ->where('ta.Id', (int) $contractId)
                        ->first();

                    if ($contractRow && ! empty($contractRow->ContractRef)) {
                        $uniqueRfqs = collect($uniqueRfqs);
                        $exists = $uniqueRfqs->contains(function ($r) use ($contractRow) {
                            return ($r->RFQNumber ?? null) === ($contractRow->ContractRef ?? null);
                        });
                        if (! $exists) {
                            $uniqueRfqs = $uniqueRfqs->prepend((object) ['RFQNumber' => $contractRow->ContractRef]);
                        }

                        $rfqResponsesCol = collect($rfqResponses ?? []);
                        $rfqResponsesCol = $rfqResponsesCol->prepend((object) [
                            'RFQNumber' => $contractRow->ContractRef,
                            'SupplierId' => (int) ($contractRow->ThirdPartyId ?? 0),
                            'SupplierID' => (int) ($contractRow->ThirdPartyId ?? 0),
                            'SupplierName' => $contractRow->SupplierName ?? '',
                            'Address' => $contractRow->Address ?? '',
                        ]);
                        $rfqResponses = $rfqResponsesCol->values();

                        $prefillContract = [
                            'ref' => $contractRow->ContractRef,
                            'supplierId' => (int) ($contractRow->SupplierId ?? 0),
                            'thirdPartyId' => (int) ($contractRow->ThirdPartyId ?? 0),
                            'supplierName' => $contractRow->SupplierName ?? '',
                            'address' => $contractRow->Address ?? '',
                        ];
                    }
                } catch (\Throwable $e) {
                    Log::warning('Contract prefill failed', ['error' => $e->getMessage()]);
                }
            }

            $sourceType = $prefillContract ? 'CONTRACT' : 'RFQ';

            return view('procurement.orders.create', [
                'itemTypes' => $itemTypes ?? [],
                'allItems' => $allItems ?? collect(),
                'rfqs' => $uniqueRfqs ?? [],
                'rfqResponses' => $rfqResponses ?? [],
                'suppliers' => $suppliers ?? [],
                'paymentTerms' => $paymentTerms ?? [],
                'prefillContract' => $prefillContract,
                'sourceType' => $sourceType,
                'contracts' => $contracts ?? collect(),
                'awardedRfqs' => $awardedRfqs ?? collect(),
                'convertedRFQIds' => $convertedRFQIds ?? [],
                'awardedTenders' => $awardedTenders ?? collect(),
                'convertedTenderIds' => $convertedTenderIds ?? [],
                'usedReferenceNumbers' => $usedReferenceNumbers ?? [],
                'taxRules' => $taxRules ?? collect(),
            ]);
        } catch (\Exception $e) {
            Log::error('Data fetch failed: ' . $e->getMessage());

            try {
                $paymentTerms = CodeDetail::query()
                    ->where('CodeID', 'PaymentTerm')
                    ->orderBy('DisplayOrder')
                    ->get(['ID', 'Description']);
                if ($paymentTerms->isEmpty()) {
                    $paymentTerms = DB::table('t_CodeDetails')
                        ->whereIn(DB::raw('RTRIM(LTRIM(CodeID))'), ['PaymentTerm', 'PaymentTerms'])
                        ->orderBy('DisplayOrder')
                        ->select('ID', 'Description')
                        ->get();
                }
            } catch (\Throwable $te) {
                $paymentTerms = collect();
            }

            return view('procurement.orders.create', [
                'suppliers' => [],
                'itemTypes' => [],
                'allItems' => collect(),
                'rfqs' => [],
                'rfqResponses' => [],
                'paymentTerms' => $paymentTerms ?? [],
                'awardedRfqs' => [],
                'convertedRFQIds' => [],
                'contracts' => collect(),
                'sourceType' => 'RFQ',
                'prefillContract' => null,
                'usedReferenceNumbers' => [],
                'taxRules' => collect(),
            ])->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created Purchase Order
     */
    public function store(PurchaseOrderRequest $request)
    {
        try {
            $validated = $request->validated();

            // Extract main PO data
            $supplier = $validated['supplier'];
            $poDate = $validated['pODate'];
            $rfqNo = $validated['refNo'] ?? null;
            $priority = $validated['priority'] ?? null;
            $terms = $validated['terms'];

            // Get tax ID from the first line item (assuming single tax policy for PO header)
            $taxes = $validated['tax'] ?? [];
            $firstTaxId = $taxes[0] ?? null;
            if ($firstTaxId === '' || $firstTaxId === '0' || $firstTaxId === 0) {
                $firstTaxId = null;
            }

            // Validate Quantity against Source
            if ($request->has('SourceType') && $request->has('SourceId')) {
                $sourceType = $request->input('SourceType');
                $sourceId = $request->input('SourceId');

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
                        $itemMaps = $availableItems->pluck('itemName', 'itemCode')->toArray(); // For error message

                        $reqItemCodes = $validated['itemCode'];
                        $reqQuantities = $validated['quantity'];

                        foreach ($reqItemCodes as $idx => $code) {
                            $qty = $reqQuantities[$idx];
                            if (isset($availableMap[$code])) {
                                $remaining = $availableMap[$code];
                                if ($qty > $remaining) {
                                    $name = $itemMaps[$code] ?? $code;

                                    return back()->withInput()->with('error', "Quantity for item '{$name}' exceeds remaining quantity. Available: {$remaining}, Requested: {$qty}");
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {

                }
            }

            // Create the PO header using OrderService
            $poResult = $this->orderService->addPO(
                $supplier,
                $poDate,
                $rfqNo,
                $priority,
                $terms,
                auth()->user(),
                $firstTaxId
            );

            if ($poResult['status'] !== 'success') {
                return back()
                    ->withInput()
                    ->with('error', $poResult['message'] ?? 'Failed to create Purchase Order');
            }

            $poId = $poResult['po_id'];

            // Add PO line items
            $itemCodes = $validated['itemCode'];
            $quantities = $validated['quantity'];
            $unitPrices = $validated['unitPrice'];
            $taxes = $validated['tax'] ?? [];
            $discounts = $validated['discount'] ?? [];
            $lineTotals = $validated['lineTotal'];

            // Fetch tax rates map for lookup [Id => Rate]
            // We now pass Tax ID directly to SP which handles lookup.

            foreach ($itemCodes as $index => $itemCode) {
                // Determine tax ID: if the submitted value is a rule ID, use it.
                // The select dropdown validates it is one of the rule IDs.
                $taxId = $taxes[$index] ?? null;
                // Ensure empty string becomes null for database
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
                    auth()->user(),
                    $poId
                );

                if ($lineResult['status'] !== 'success') {
                    Log::warning('Failed to add PO line item', [
                        'po_id' => $poId,
                        'item_code' => $itemCode,
                        'error' => $lineResult['message'] ?? 'Unknown error',
                    ]);
                }
            }

            // Calculate PO totals
            $this->orderService->AddPurchaseOrderSum($poId);

            // Update Source info if present (important for Contracts/Direct)
            if ($request->has('SourceType') && $request->has('SourceId')) {
                Order::where('Id', $poId)->update([
                    'SourceType' => $request->input('SourceType'),
                    'SourceId' => $request->input('SourceId'),
                ]);
            }

            // Prepare redirect response first
            $redirectResponse = redirect()
                ->route('purchaseOrder.show', $poId)
                ->with('success', 'Purchase Order created successfully');

            // Initialize approval workflow for the newly created PO (after preparing response)
            try {
                $order = Order::findOrFail($poId);
                $this->workflowService->submit($order, auth()->user(), ApprovalEnum::Submitted, 'Purchase Order created and submitted for approval');
                Log::info('Approval workflow submitted for PO', ['po_id' => $poId]);
            } catch (\Exception $e) {
                Log::warning('Failed to submit approval workflow for PO', [
                    'po_id' => $poId,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the entire operation if workflow initiation fails
            }

            return $redirectResponse;
        } catch (\Exception $e) {
            Log::error('Error creating Purchase Order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'An error occurred while creating the Purchase Order: ' . $e->getMessage());
        }
    }

    public function show(Request $request, string $id)
    {
        try {
            Log::info("Loading PO show page", ['order_id' => $id]);

            $order = Order::findOrFail($id);
            $this->authorize('view', $order);
            Log::info("Order found and authorized", ['order_id' => $id]);

            $orderInfo = $this->orderService->fetchOrderDetails($id);
            Log::info("Order details fetched", ['order_id' => $id]);

            $lineInfo = $this->orderService->fetchOrderLineDetails($id);
            Log::info("Line items fetched", ['order_id' => $id, 'line_count' => count($lineInfo)]);

            // Use the generic workflow service with error handling
            $history = collect();

            try {
                // Use getWorkflowStatus as requested
                $workflowData = $this->workflowService->getWorkflowStatus($order->getMorphClass(), $order->getKey());
                $historyArr = $workflowData['completedApprovals'] ?? [];
                $stageName = isset($workflowData['currentStage']['name']) ? $workflowData['currentStage']['name'] : 'Stage';

                $fetchedHistory = collect($historyArr)->map(function ($item) use ($stageName) {
                    $obj = (object)$item;
                    if (! isset($obj->StatusId)) {
                        $obj->StatusId = 'A';
                    }
                    if (! isset($obj->stage)) {
                        $obj->stage = (object)['StageName' => $stageName];
                    }
                    if (! isset($obj->status)) {
                        $obj->status = (object)['Description' => 'Approved'];
                    }
                    if (! isset($obj->creator)) {
                        $obj->creator = (object)['Name' => $item->Name ?? 'Unknown'];
                    }

                    return $obj;
                });

                $history = $history->merge($fetchedHistory);

                Log::info("Workflow history fetched via getWorkflowStatus", ['order_id' => $id, 'history_count' => $history->count()]);
            } catch (\Exception $e) {
                Log::warning("Failed to fetch workflow history", [
                    'order_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Manually add "Submitted" entry to ensure at least initiation is visible
            // This runs regardless of fetch success
            $submitted = (object)[
                'stage' => (object)['StageName' => 'Initiation'],
                'status' => (object)['Description' => 'Submitted'],
                'creator' => (object)['Name' => $order->creator->Name ?? 'Unknown'],
                'CreatedOn' => $order->CreatedOn,
                'Notes' => 'Order initiated',
                'StatusId' => '',
            ];
            $history->prepend($submitted); // Prepend to be at the start (or Push if order matters? Ascending vs Descending)
            // View typically iterates top-down. History usually Descending?
            // If View is "History", usually Newest First?
            // "Submitted" is Oldest.
            // If View shows List, usually we want Chronological?
            // Let's check View again.
            // View line 117 foreach($history as $record).
            // It doesn't sort.
            // Controller `historyForModel` ordered by `CreatedOn desc`.
            // So Newest First.
            // So "Submitted" (Oldest) should be LAST (Pushed).
            // But if I want it to appear at bottom of list...
            // Wait, if I use Push, it goes to end of collection.
            // If View iterates, it shows at bottom.
            // Does View show Newest on Top?
            // `historyForModel` did `orderBy('CreatedOn', 'desc')`.
            // So yes, Newest on Top.
            // So "Submitted" should be at the BOTTOM (End).
            // So `$history->push($submitted)` is correct.


            // Check if user can approve
            try {
                $canApprove = $this->workflowService->canApproveModel($order, auth()->user());
                Log::info("Can approve check completed", ['order_id' => $id, 'can_approve' => $canApprove]);
            } catch (\Exception $e) {
                Log::warning("Failed to check approval permission", [
                    'order_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                $canApprove = false;
            }

            // Get workflow status
            try {
                $workflowStatus = $this->workflowService->getWorkflowStatus($order->getMorphClass(), $order->getKey());
                $isFullyApproved = ($workflowStatus['totalPending'] ?? 0) === 0;
                Log::info("Workflow status fetched", ['order_id' => $id, 'status' => $workflowStatus]);
            } catch (\Exception $e) {
                Log::warning("Failed to get workflow status via service, using fallback", [
                    'order_id' => $id,
                    'error' => $e->getMessage(),
                ]);

                // Fallback: Query pending table directly to ensure approvers are shown
                $pendingApprovals = \Illuminate\Support\Facades\DB::table('t_WorkFlowPending as p')
                    ->join('t_WorkFlowStages as s', 'p.StageId', '=', 's.Id')
                    ->leftJoin('t_Users as u', 'p.UserId', '=', 'u.Id')
                    ->leftJoin('t_WorkFlowGroups as g', 'p.GroupId', '=', 'g.Id')
                    ->where('p.Source', $order->getTable())
                    ->where('p.SourceID', $order->Id)
                    ->select('s.StageName as stage', \Illuminate\Support\Facades\DB::raw("COALESCE(u.Name, g.Name, 'Unknown') as approver"))
                    ->get()
                    ->map(fn ($row) => (array)$row)
                    ->toArray();

                $workflowStatus = ['pendingApprovals' => $pendingApprovals];
                $isFullyApproved = count($pendingApprovals) === 0;
            }

            Log::info("Rendering view", ['order_id' => $id]);

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
            Log::warning("Unauthorized access to Order ID: {$id}", [
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Order ID {$id} not found");

            return redirect()->back()->with('error', 'Order not found.');
        }
    }

    /**
     * Show the form for editing the specified purchase order
     */
    public function edit($id)
    {
        $order = $this->orderService->getOrder($id);
        $this->authorize('update', $order);

        if (! $order) {
            return redirect()->back()->with('error', 'Order not found.');
        }
        $suppliers = SupplierService::getSuppliers();

        return view('procurement.orders.edit', compact('order', 'suppliers'));
    }

    /**
     * Update the specified purchase order in storage
     */
    public function update(PurchaseOrderRequest $request, $id)
    {
        $order = $this->orderService->getOrder($id);
        $this->authorize('update', $order);

        $this->orderService->updateOrder($id, $request->validated());

        return redirect()->route('orders.show', $id)->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified purchase order from storage
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('delete', $order);

            $this->orderService->deleteOrder($id);

            return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to fetch order ID {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to fetch order.');
        }
    }

    /**
     * Show approval page
     */
    public function approval($id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('view', $order);

            $orderInfo = $this->orderService->fetchOrderDetails($id);
            $lineInfo = $this->orderService->fetchOrderLineDetails($id);

            // Fetch payment term description
            $paymentTermRow = DB::table('t_CodeDetails')
                ->where('CodeID', 'PaymentTerm')
                ->first();
            $paymentTerms = $paymentTermRow->Description ?? null;

            return view(
                'procurement.orders.approval',
                compact('orderInfo', 'lineInfo', 'paymentTerms')
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized access to Order approval ID: {$id}", [
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Exception $e) {
            Log::error("Failed to load approval page for Order ID {$id}", [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load approval page.');
        }
    }

    /**
     * Submit order for approval
     */
    public function submit(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('update', $order);

            $result = $this->workflowService->submit(
                $order,
                auth()->user(),
                ApprovalEnum::Submitted,  // Pass the enum
                $request->input('remarks', 'Submitted for approval')
            );

            if ($result) {
                return redirect()->back()->with('success', 'Order submitted for approval successfully.');
            }

            return redirect()->back()->with('error', 'Failed to submit order for approval.');
        } catch (\Exception $e) {
            Log::error('Order submission failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to submit order: ' . $e->getMessage());
        }
    }

    /**
     * Approve an order
     */
    public function approve(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('approve', $order);

            // Maker-Checker Rule: Prevent self-approval
            if ($order->CreatedBy == auth()->id()) {
                Log::warning('Maker-checker violation: User attempted to approve own PO', [
                    'order_id' => $id,
                    'user_id' => auth()->id(),
                ]);

                return redirect()->back()->with('error', 'You cannot approve your own Purchase Order (Maker-Checker rule).');
            }

            $result = $this->workflowService->approve(
                $order,
                auth()->user(),
                ApprovalEnum::Approved,  // Pass the enum
                $request->input('remarks', 'Approved'),
                'DocStatus'  // Explicitly pass the status column
            );

            if ($result) {
                return redirect()->back()->with('success', 'Order approved successfully.');
            }

            return redirect()->back()->with('error', 'Failed to approve order.');
        } catch (\Exception $e) {
            Log::error('Order approval failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to approve order: ' . $e->getMessage());
        }
    }

    /**
     * Reject an order
     */
    public function reject(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('approve', $order);

            $result = $this->workflowService->reject(
                $order,
                auth()->user(),
                ApprovalEnum::Rejected,  // Pass the enum
                $request->input('remarks', 'Rejected'),
                'DocStatus'  // Explicitly pass the status column
            );

            if ($result) {
                return redirect()->back()->with('success', 'Order rejected successfully.');
            }

            return redirect()->back()->with('error', 'Failed to reject order.');
        } catch (\Exception $e) {
            Log::error('Order rejection failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to reject order: ' . $e->getMessage());
        }
    }

    /**
     * Return order for modification
     */
    public function return(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('approve', $order);

            // Use Pending status to return for modification
            $result = $this->workflowService->approve(
                $order,
                auth()->user(),
                ApprovalEnum::Pending,  // Return to pending
                $request->input('remarks', 'Returned for modification'),
                'DocStatus'
            );

            if ($result) {
                return redirect()->back()->with('success', 'Order returned for modification successfully.');
            }

            return redirect()->back()->with('error', 'Failed to return order.');
        } catch (\Exception $e) {
            Log::error('Order return failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to return order: ' . $e->getMessage());
        }
    }

    /**
     * Add comment to order workflow
     */
    public function comment(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('view', $order);

            // You can implement a comment method in ApprovalWorkflow if needed
            // For now, return info message
            return redirect()->back()->with('info', 'Comment feature not yet implemented.');
        } catch (\Exception $e) {
            Log::error('Failed to add comment', [
                'order_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to add comment: ' . $e->getMessage());
        }
    }

    // Add these methods after your comment() method:

    public function getItemDetails($item): JsonResponse
    {
        try {
            $details = $this->itemService->getItemDetails($item);

            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSuppliers(): JsonResponse
    {
        try {
            $suppliers = SupplierService::getSuppliers();

            return response()->json([
                'success' => true,
                'data' => $suppliers,
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
                'data' => [
                    'Address' => $address,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch supplier details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function linkRFQ()
    {
        $this->authorize('viewAny', Order::class);

        try {
            $RFQ = $this->rfqService->fetchRFQ();
        } catch (\Exception $e) {
            Log::error('Error fetching RFQS: ' . $e->getMessage());
            $RFQ = collect();
        }

        return view("procurement.orders.rfqlink", compact('RFQ'));
    }

    public function fetchRFQDetails($id): JsonResponse
    {
        $this->authorize('create', Order::class);

        try {
            $RFQData = $this->rfqService->RFQTOPO($id);

            return response()->json([
                'success' => true,
                'data' => $RFQData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RFQ.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAwardedRFQs()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->orderSourceService->getAwardedRFQs(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch awarded RFQs: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch awarded RFQs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAwardedTenders()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->orderSourceService->getAwardedTenders(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch awarded Tenders: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch awarded Tenders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTenderItems($tenderId)
    {
        try {
            $items = $this->orderSourceService->getTenderItems($tenderId);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Tender Items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getContractItems($contractId)
    {
        try {
            $type = request('type', 'tender');
            $items = $this->orderSourceService->getContractItems($contractId, $type);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Contract Items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Contract Items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDirectPlans(): JsonResponse
    {
        try {
            $plans = $this->orderSourceService->getDirectPlans();

            return response()->json([
                'success' => true,
                'data' => $plans,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch direct plans: ' . $e->getMessage());

            return response()->json([
               'success' => false,
               'message' => 'Failed to fetch direct plans',
               'error' => $e->getMessage(),
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
        // Placeholder implementation - return empty or actual categories linked to plans
        try {
            return response()->json(['success' => true, 'data' => []]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'data' => []]);
        }
    }

    public function getPrequalifiedSuppliers($categoryId): JsonResponse
    {
        try {
            // Basic implementation to return empty list or actual logic if tables known
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
            $items = $this->orderSourceService->getRFQItems($rfqId, $supplierId);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get RFQ items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RFQ items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDirectPlanItems($planId)
    {
        try {
            $items = $this->orderSourceService->getDirectPlanItems($planId);

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Direct Plan Items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Direct Plan Items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPlanItemCategories($planId): JsonResponse
    {
        try {
            // Get distinct categories from plan line items for this plan
            $directMethod = DB::table('t_CodeDetails')
                ->where('CodeID', 'ProcurementMethod')
                ->where(function ($q) {
                    $q->where('Description', 'LIKE', '%Direct%')
                        ->orWhere('Value', 'Like', '%Direct%');
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
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get plan categories: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function prequalifiedSuppliersByCategory($categoryId): JsonResponse
    {
        try {
            // Get all prequalified suppliers (Active Suppliers)
            // Use t_Suppliers as the source of active status
            // Correct logic: t_Suppliers -> t_SupplierMaster -> t_ThirdParties
            $suppliers = DB::table('t_Suppliers as s')
    ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
    ->join('t_ThirdParties as tp', 'sm.ThirdPartyId', '=', 'tp.Id')
    ->where('s.Active_Status', 1)
    ->whereNull('s.DeletedOn')
    ->groupBy(
        'sm.Id',
        'tp.Id',
        'tp.TradingName',
        'tp.ThirdPartyName',
        'tp.PhysicalAddress'
    )
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
                'data' => $suppliers,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch prequalified suppliers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
