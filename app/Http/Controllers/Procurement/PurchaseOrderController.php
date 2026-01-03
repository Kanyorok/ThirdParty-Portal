<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Http\Requests\Orders\PurchaseOrderRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\Order;

use App\Services\Workflow\ApprovalWorkflow;  // Changed from PurchaseOrderWorkflowService
use App\Services\Core\DocumentApprovalService;
use App\Services\Procurement\Items\ItemService;
use App\Services\Procurement\Orders\OrderService;
use App\Services\ThirdParties\SupplierService;  
use App\Services\Procurement\RFQ\RFQService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Services\Procurement\Orders\PurchaseOrderWorkflowService;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected ItemService $itemService,
        protected OrderService $orderService,
        protected DocumentApprovalService $documentApprovalService,
        protected RFQService $rfqService,
        protected ApprovalWorkflow $workflowService  // Changed type hint
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

            // Get awarded RFQs
            $awardedFromRFQAward = collect();
            try {
                $awardedFromRFQAward = DB::table('t_RFQAward as a')
                    ->join('t_RFQ as r', 'a.RFQId', '=', 'r.Id')
                    ->leftJoin('t_Suppliers as s', 's.Id', '=', 'a.SupplierId')
                    ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                    ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                    ->select(
                        'r.Id',
                        'r.RFQNumber',
                        'a.SupplierId',
                        DB::raw('tp.Id as ThirdPartyId'),
                        DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                        DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                    )
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Skipping RFQAward join for awarded RFQs', ['error' => $e->getMessage()]);
            }

            $approvedConvertedRFQIds = DB::table('t_Orders')
                ->whereRaw("RTRIM(LTRIM(ISNULL(SourceType,'')))='RFQ'")
                ->whereNotNull('SourceId')
                ->where('DocStatus', 'a')
                ->pluck('SourceId')
                ->toArray();

            $usedReferenceNumbers = DB::table('t_Orders')
                ->whereNotNull('ExtOrdNum')
                ->where('DocStatus', 'a')
                ->pluck('ExtOrdNum')
                ->map(function ($v) {
                    return is_null($v) ? '' : trim((string)$v);
                })
                ->filter()
                ->values()
                ->toArray();

            $awardedRfqs = $awardedFromRFQAward
                ->filter(function ($r) use ($approvedConvertedRFQIds, $usedReferenceNumbers) {
                    $rfqNo = trim((string)($r->RFQNumber ?? ''));
                    return !in_array($r->Id, $approvedConvertedRFQIds) && !in_array($rfqNo, $usedReferenceNumbers);
                })
                ->unique('Id')
                ->values();

            $convertedRFQIds = DB::table('t_Orders')
                ->whereRaw("RTRIM(LTRIM(ISNULL(SourceType,'')))='RFQ'")
                ->whereNotNull('SourceId')
                ->where('DocStatus', 'a')
                ->pluck('SourceId')
                ->toArray();

            // Get awarded tenders
            $awardedTenders = collect();
            try {
                $awardedTenders = DB::table('t_TenderAwards as ta')
                    ->join('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
                    ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
                    ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                    ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                    ->where('ta.AwardStatus', 'Approved')
                    ->where(function ($q) {
                        $q->whereNull('ta.ContractStatus')
                            ->orWhere('ta.ContractStatus', '')
                            ->orWhere('ta.ContractStatus', 'No Contract Required');
                    })
                    ->whereRaw("NOT EXISTS (SELECT 1 FROM t_Orders o WHERE RTRIM(LTRIM(ISNULL(o.SourceType,'')))='TENDER' AND o.DocStatus='a' AND o.SourceId = t.Id)")
                    ->select(
                        't.Id',
                        't.TenderNo',
                        DB::raw('ta.WinningSupplierID as SupplierId'),
                        DB::raw('tp.Id as ThirdPartyId'),
                        DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                        DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                    )
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Skipping TenderAwards join', ['error' => $e->getMessage()]);
            }

            $convertedTenderIds = DB::table('t_Orders')
                ->whereRaw("RTRIM(LTRIM(ISNULL(SourceType,'')))='TENDER'")
                ->whereNotNull('SourceId')
                ->where('DocStatus', 'a')
                ->pluck('SourceId')
                ->toArray();

            // Get active contracts
            $contracts = DB::table('t_TenderAwards as ta')
                ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
                ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                ->whereIn('ta.ContractStatus', ['Approved', 'Executed'])
                ->whereNotNull('ta.ContractRef')
                ->where('ta.ContractRef', '!=', '')
                ->whereRaw("NOT EXISTS (SELECT 1 FROM t_Orders o WHERE RTRIM(LTRIM(ISNULL(o.SourceType,'')))='CONTRACT' AND o.DocStatus='a' AND o.SourceId = ta.Id)")
                ->orderByDesc('ta.ContractApprovedOn')
                ->select([
                    'ta.Id as Id',
                    'ta.ContractRef as ContractRef',
                    'ta.WinningSupplierID as SupplierId',
                    DB::raw('tp.Id as ThirdPartyId'),
                    DB::raw("tp.TradingName as SupplierName"),
                    DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                    'ta.ContractStatus'
                ])
                ->get();

            // Contract prefill support
            $prefillContract = null;
            $contractId = request('contractId');
            if (!empty($contractId)) {
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

                    if ($contractRow && !empty($contractRow->ContractRef)) {
                        $uniqueRfqs = collect($uniqueRfqs);
                        $exists = $uniqueRfqs->contains(function ($r) use ($contractRow) {
                            return ($r->RFQNumber ?? null) === ($contractRow->ContractRef ?? null);
                        });
                        if (!$exists) {
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
            
            // Create the PO header using OrderService
            $poResult = $this->orderService->addPO(
                $supplier,
                $poDate,
                $rfqNo,
                $priority,
                $terms,
                auth()->user()
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
            
            foreach ($itemCodes as $index => $itemCode) {
                $lineResult = $this->orderService->addPOLines(
                    $itemCode,
                    $quantities[$index],
                    $unitPrices[$index],
                    $taxes[$index] ?? 0,
                    $discounts[$index] ?? 0,
                    $lineTotals[$index],
                    auth()->user(),
                    $poId
                );
                
                if ($lineResult['status'] !== 'success') {
                    Log::warning('Failed to add PO line item', [
                        'po_id' => $poId,
                        'item_code' => $itemCode,
                        'error' => $lineResult['message'] ?? 'Unknown error'
                    ]);
                }
            }
            
            // Calculate PO totals
            $this->orderService->AddPurchaseOrderSum($poId);
            
            // Initialize approval workflow for the newly created PO
            try {
                $order = Order::findOrFail($poId);
                $this->workflowService->initiate($order, auth()->user());
                Log::info('Approval workflow initiated for PO', ['po_id' => $poId]);
            } catch (\Exception $e) {
                Log::warning('Failed to initiate approval workflow for PO', [
                    'po_id' => $poId,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the entire operation if workflow initiation fails
            }
            
            return redirect()
                ->route('purchaseOrder.show', $poId)
                ->with('success', 'Purchase Order created successfully');
                
        } catch (\Exception $e) {
            Log::error('Error creating Purchase Order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'An error occurred while creating the Purchase Order: ' . $e->getMessage());
        }
    }

    public function show(Request $request, string $id)
    {
        try {
            $order = Order::findOrFail($id);
            $this->authorize('view', $order);

            $orderInfo = $this->orderService->fetchOrderDetails($id);
            $lineInfo = $this->orderService->fetchOrderLineDetails($id);

            // Use the generic workflow service
            $history = $this->workflowService->historyForModel($order);

            // Check if user can approve
            $canApprove = $this->workflowService->canApproveModel($order, auth()->user());

            // Get workflow status
            $workflowStatus = $this->workflowService->getStatus($order);
            $isFullyApproved = !isset($workflowStatus['pending']) || $workflowStatus['pending'] === 0;

            if ($request->ajax()) {
                return view(
                    'procurement.orders.partials.show_content',
                    compact('orderInfo', 'lineInfo', 'history', 'canApprove', 'isFullyApproved')
                )->render();
            }

            return view(
                'procurement.orders.show',
                compact('orderInfo', 'lineInfo', 'history', 'canApprove', 'isFullyApproved')
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning("Unauthorized access to Order ID: {$id}", [
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Order ID {$id} not found");
            return redirect()->back()->with('error', 'Order not found.');
        } catch (\Exception $e) {
            Log::error("Failed to fetch order ID {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
                'user_id' => auth()->id()
            ]);
            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Exception $e) {
            Log::error("Failed to load approval page for Order ID {$id}", [
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
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
                'message' => 'Failed to fetch suppliers.'
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
            // Get awarded RFQs
            $awardedFromRFQAward = collect();
            try {
                $awardedFromRFQAward = DB::table('t_RFQAward as a')
                    ->join('t_RFQ as r', 'a.RFQId', '=', 'r.Id')
                    ->leftJoin('t_Suppliers as s', 's.Id', '=', 'a.SupplierId')
                   ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                    ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                    ->select(
                        'r.Id',
                        'r.RFQNumber',
                        'a.SupplierId',
                        DB::raw('tp.Id as ThirdPartyId'),
                        DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                        DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                    )
                    ->get();
            } catch (\Throwable $e) {
                Log::warning('Skipping RFQAward join for awarded RFQs', ['error' => $e->getMessage()]);
            }

            $approvedConvertedRFQIds = DB::table('t_Orders')
                ->whereRaw("RTRIM(LTRIM(ISNULL(SourceType,'')))='RFQ'")
                ->whereNotNull('SourceId')
                ->where('DocStatus', 'a')
                ->pluck('SourceId')
                ->toArray();

            $usedReferenceNumbers = DB::table('t_Orders')
                ->whereNotNull('ExtOrdNum')
                ->where('DocStatus', 'a')
                ->pluck('ExtOrdNum')
                ->map(function ($v) {
                    return is_null($v) ? '' : trim((string)$v);
                })
                ->filter()
                ->values()
                ->toArray();

            $awardedRfqs = $awardedFromRFQAward
                ->filter(function ($r) use ($approvedConvertedRFQIds, $usedReferenceNumbers) {
                    $rfqNo = trim((string)($r->RFQNumber ?? ''));
                    return !in_array($r->Id, $approvedConvertedRFQIds) && !in_array($rfqNo, $usedReferenceNumbers);
                })
                ->unique('Id')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $awardedRfqs
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch awarded RFQs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch awarded RFQs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAwardedTenders()
    {
        try {
            $awardedTenders = DB::table('t_TenderAwards as ta')
                ->join('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
                ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
                ->leftJoin('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
                ->where('ta.AwardStatus', 'Approved')
                ->where(function ($q) {
                    $q->whereNull('ta.ContractStatus')
                        ->orWhere('ta.ContractStatus', '')
                        ->orWhere('ta.ContractStatus', 'No Contract Required');
                })
                ->whereRaw("NOT EXISTS (SELECT 1 FROM t_Orders o WHERE RTRIM(LTRIM(ISNULL(o.SourceType,'')))='TENDER' AND o.DocStatus='a' AND o.SourceId = t.Id)")
                ->select(
                    't.Id',
                    't.TenderNo',
                    DB::raw('ta.WinningSupplierID as SupplierId'),
                    DB::raw('tp.Id as ThirdPartyId'),
                    DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                    DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                )
                ->get();

            return response()->json([
                'success' => true,
                'data' => $awardedTenders
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch awarded Tenders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch awarded Tenders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTenderItems($tenderId)
    {
        try {
            // Check if tender exists
            $tender = DB::table('t_Tenders')->where('Id', $tenderId)->first();
            if (!$tender) {
                return response()->json(['success' => false, 'message' => 'Tender not found'], 404);
            }

            // Fetch items using logic similar to TenderController
            $items = DB::table('t_TenderItems as ti')
                ->join('t_Items as i', 'ti.ItemID', '=', 'i.Id')
                ->leftJoin('t_Pricing as ip', 'i.Id', '=', 'ip.ItemID')
                ->where('ti.TenderID', $tenderId)
                ->select(
                    'i.Id as itemCode',
                    'i.ItemName as itemName',
                    'ti.QtyToTender as quantity',
                    DB::raw('COALESCE(ip.ActualPrice, 0) as unitPrice'),
                    DB::raw('(ti.QtyToTender * COALESCE(ip.ActualPrice, 0)) as lineTotal'),
                    'ti.Remarks as description'
                )
                ->distinct()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Tender Items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Tender Items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getContractItems($contractId)
    {
        try {
            // Find contract (TenderAward)
            $contract = DB::table('t_TenderAwards')->where('Id', $contractId)->first();

            if (!$contract) {
                return response()->json(['success' => false, 'message' => 'Contract not found'], 404);
            }

            // Logic: Contracts are usually based on Tenders or RFQs.
            // If TenderID is present, fetch Tender Items.
            if (!empty($contract->TenderID)) {
                return $this->getTenderItems($contract->TenderID);
            }
            // If RFQID is present (assuming column name based on context, though strict schema check would be better)
            // Based on TenderAward model, there is no explicit RFQID, but let's check if there is an alternative logic
            // For now, if no TenderID, return empty or try to find items linked to the Award directly if such table exists.
            
             return response()->json([
                'success' => true,
                'data' => [] 
            ]);

        } catch (\Exception $e) {
             Log::error('Failed to fetch Contract Items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Contract Items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDirectPlans(): JsonResponse
    {
        try {
            // Find 'Direct' procurement method ID from t_CodeDetails
            $directMethod = DB::table('t_CodeDetails')
                ->where('CodeID', 'ProcurementMethod')
                ->where(function ($q) {
                    $q->where('Description', 'LIKE', '%Direct%')
                      ->orWhere('Value', 'Like', '%Direct%');
                })
                ->first();

            $methodId = $directMethod ? $directMethod->ID : null;

            if (!$methodId) {
                return response()->json(['success' => true, 'data' => []]);
            }

            // Fetch plans that have line items with Direct method
            // We use t_PlanLineItem to find the relevant PlanIDs
            $planIds = DB::table('t_PlanLineItem')
                ->where('ProcurementMethod', $methodId)
                ->pluck('PlanID')
                ->unique()
                ->toArray();

            if (empty($planIds)) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $plans = ConsolidatedProcurementPlan::query()
                ->whereIn('PlanID', $planIds)
                ->where('Status', 'Ap') // Use status code, not full word
                ->get()
                ->map(function ($plan) {
                     return [
                         'PlanID' => $plan->PlanID, // Ensure correct casing
                         'Title' => $plan->Title ?? $plan->Description ?? ('Plan #' . $plan->PlanID),
                         'FiscalYear' => $plan->FiscalYear,
                         'PendingItems' => 0 // Placeholder, calculated properly below
                     ];
                });

            // Calculate pending items correctly
             $plans = $plans->map(function($p) use ($methodId) {
                 $p['PendingItems'] = DB::table('t_PlanLineItem')
                        ->where('PlanID', $p['PlanID'])
                        ->where('ProcurementMethod', $methodId)
                        ->count();
                 return $p;
             });

            return response()->json([
                'success' => true,
                'data' => $plans
            ]);
        } catch (\Exception $e) {
             Log::error('Failed to fetch direct plans: ' . $e->getMessage());
             return response()->json([
                'success' => false,
                'message' => 'Failed to fetch direct plans',
                'error' => $e->getMessage()
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
            // Re-use RFQTOPO reasoning to get items
            $rfqData = $this->rfqService->RFQTOPO($rfqId);
            
            // RFQTOPO returns an object (the RFQ record) with an 'items' property which is an array
            $items = $rfqData->items ?? [];

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get RFQ items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RFQ items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDirectPlanItems($planId)
    {
        try {
            $directMethod = DB::table('t_CodeDetails')
                ->where('CodeID', 'ProcurementMethod')
                ->where(function ($q) {
                    $q->where('Description', 'LIKE', '%Direct Purchase%')
                      ->orWhere('Value', 'Like', '%D%');
                })
                ->value('ID');

            // Join with t_Items to get item details
            $items = DB::table('t_PlanLineItem as pli')
                ->leftJoin('t_Items as i', 'pli.ItemID', '=', 'i.Id')
                ->where('pli.PlanID', $planId)
                ->where('pli.ProcurementMethod', $directMethod)
                ->whereNull('pli.DeletedOn')
                ->select(
                    'pli.LineItemID as id',
                    'pli.ItemID as itemCode',
                    'i.ItemName as itemName',
                    'i.ItemDescription as description',
                    'pli.MergedQty as quantity',
                    'pli.EstimatedUnitCost as unitPrice',
                    'pli.CategoryID'
                )
                ->get();

            // Calculate totals
            $items = $items->map(function($item) {
                $item->lineTotal = ($item->quantity ?? 0) * ($item->unitPrice ?? 0);
                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => $items
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get Direct Plan items: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plan items',
                'error' => $e->getMessage()
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
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get plan categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
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
                ->select(
                    's.Id as SupplierId',
                    'tp.Id as ThirdPartyId',
                    DB::raw("COALESCE(tp.TradingName, tp.ThirdPartyName, '') as SupplierName"),
                    DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                )
                ->distinct()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch prequalified suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suppliers',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
