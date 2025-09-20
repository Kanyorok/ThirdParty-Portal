<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Http\Requests\Orders\PurchaseOrderRequest;
use App\Models\Auth\User;
use App\Models\Procurement\Order;
use App\Models\Procurement\TenderAward;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderItems;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItem;
use App\Services\Core\ApprovalService;
use App\Services\Core\DocumentApprovalService;
use App\Services\Procurement\Items\ItemService;
use App\Services\Procurement\Orders\OrderService;
use App\Services\Procurement\RFQ\RFQService;
use App\Services\ThirdParty\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log; // using global \Log facade calls inline to avoid import confusion
use Illuminate\Support\Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Models\Core\CodeDetail;
use App\Models\ThirdParty\SupplierCategory;

class PurchaseOrderController extends Controller
{
    public function __construct(protected ItemService $itemService, protected SupplierService $supplierService, protected OrderService $orderService, protected RFQService $rfqService, protected DocumentApprovalService $documentApprovalService)
    {
        $this->middleware('ajax')->except([
            'index',
            'create',
            'show',
            'linkRFQ',
            'fetchRFQDetails',
            'approval',
            'approve',
            // Exempt commonly used GET JSON endpoints from strict AJAX header requirement
            'getItemDetails',
            'getSuppliers',
            'getSupplierDetails',
            'prequalifiedSuppliersByCategory',
            'getRFQItems',
            'getAwardedRFQs',
        ]);
//        $this->authorizeResource(Order::class);
    }

    public function getItemDetails($item): JsonResponse
    {
        try{
            $details = $this->itemService->getItemDetails($item);
            return response()->json([
                'success' => true,
                'data' => $details,
            ]);}
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSupplierDetails($supplier): JsonResponse
    {
        try {
            $details = $this->supplierService->getSupplierDetails($supplier);
            // Ensure the response has an Address key for the frontend
            $address = '';
            if ($details) {
                // If $details is an array or object, try to get Address
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
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSuppliers(): JsonResponse

    {

        try {
            $suppliers = $this->supplierService->getSuppliers();
            \Log::info('Suppliers data:', $suppliers->toArray());
            return response()->json([
                'success' => true,
                'data' => $suppliers,
            ]);}
        catch(\Exception $e){
            \Log::error('Error fetching suppliers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()

    {

//        User::query()->hasPermission(PermissionEnum::Users->value)->dd();

        try {
            $details = $this->orderService->fetchOrders();
            // Debug: log the details to storage/logs/laravel.log
            \Log::info('PurchaseOrderController@index details:', ['details' => $details]);
            // Optionally, uncomment the next line to dump to browser (remove after checking)
            // dd($details);
            return view('procurement.orders.index', compact('details'));
        } catch (\Exception $e) {
            \Log::error('Create page failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch items: ' . $e->getMessage());
        }
//        return view("procurement.orders.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Initialize collections
        $itemTypes = collect();
        $rfqResponses = collect();
        $uniqueRfqs = collect();
        $suppliers = collect();
        $paymentTerms = collect();

        // Try to get existing RFQ data (handle failures gracefully)
        try {
            $itemTypes = $this->itemService->getTypes();
        } catch (\Exception $e) {
            Log::warning('ItemService failed: ' . $e->getMessage());
        }

        try {
            $rfqResponses = $this->rfqService->fetchRFQ();
            $uniqueRfqs = collect($rfqResponses)->unique('RFQNumber')->values();
        } catch (\Exception $e) {
            Log::warning('RFQService failed: ' . $e->getMessage());
        }

        try {
            $suppliers = $this->supplierService->getSuppliers();
        } catch (\Exception $e) {
            Log::warning('SupplierService failed: ' . $e->getMessage());
        }

        try {
            $paymentTerms = CodeDetail::where('CodeID', 'PaymentTerm')->get(['ID', 'Description']);
        } catch (\Exception $e) {
            Log::warning('PaymentTerms failed: ' . $e->getMessage());
        }

        // Get unified origination data (these should work)
        try {
            $awards = $this->getAvailableAwards();
        } catch (\Exception $e) {
            Log::error('getAvailableAwards failed: ' . $e->getMessage());
            $awards = collect();
        }

        try {
            $contracts = $this->getAvailableContracts();
        } catch (\Exception $e) {
            Log::error('getAvailableContracts failed: ' . $e->getMessage());
            $contracts = collect();
        }

        try {
            $procurementPlans = $this->getAvailableProcurementPlans();
        } catch (\Exception $e) {
            Log::error('getAvailableProcurementPlans failed: ' . $e->getMessage());
            $procurementPlans = collect();
        }

        return view('procurement.orders.create', [
            'itemTypes' => $itemTypes,
            'rfqs' => $uniqueRfqs,
            'rfqResponses' => $rfqResponses,
            'suppliers' => $suppliers,
            'paymentTerms' => $paymentTerms,
            // NEW: Unified origination data
            'awards' => $awards,
            'contracts' => $contracts,
            'procurementPlans' => $procurementPlans,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseOrderRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            // Check if this is a unified origination form
            if ($request->has('origination_type')) {
                return $this->storeUnifiedOrder($request, $validatedData);
            }

            $actor = $request->user();
            if (!$actor) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Ensure terms is a valid ID from t_CodeDetails
            if (!DB::table('t_CodeDetails')->where('ID', $validatedData['terms'])->where('CodeID', 'PaymentTerm')->exists()) {
                \Log::error('Invalid payment term ID provided.', [
                    'terms' => $validatedData['terms'],
                    'user_id' => $actor->Id ?? null,
                ]);
                return response()->json([
                    'message' => 'Invalid payment term selected.',
                    'error' => 'The selected payment term does not exist.'
                ], 422);
            }

            // Pass the terms ID (from t_CodeDetails.ID) to addPO
            $POAdd = $this->orderService->addPO(
                $validatedData['supplier'],
                $validatedData['pODate'],
                $validatedData['refNo'],
                $validatedData['priority'],
                $validatedData['terms'], // This is the ID from t_CodeDetails
                $actor
            );

            if ($POAdd['status'] !== 'success') {
                \Log::error('Failed to create PO.', [
                    'input' => $validatedData,
                    'user_id' => $actor->Id ?? null,
                    'service_response' => $POAdd,
                ]);

                return response()->json([
                    'message' => $POAdd['message'] ?? 'Failed to create purchase order',
                    'error' => $POAdd['error'] ?? 'Unknown error'
                ], 500);
            }

            $poId = $POAdd['po_id'] ?? null;

            if (!$poId) {
                \Log::error('PO created but no ID returned.', [
                    'response' => $POAdd
                ]);

                return response()->json([
                    'message' => 'Purchase order created but no ID returned.',
                    'error' => 'Missing PO ID'
                ], 500);
            }

            // If Source fields provided, update order header after creation
            if (!empty($validatedData['SourceType']) && !empty($validatedData['SourceId'])) {
                DB::table('t_Orders')->where('Id', $poId)->update([
                    'SourceType' => $validatedData['SourceType'],
                    'SourceId' => $validatedData['SourceId'],
                ]);
            }

            // Process each PO line
            foreach ($validatedData['itemCode'] as $index => $itemCode) {
                $POLinesAdd = $this->orderService->addPOLines(
                    $itemCode,
                    $validatedData['quantity'][$index],
                    $validatedData['unitPrice'][$index],
                    $validatedData['tax'][$index],
                    $validatedData['discount'][$index],
                    $validatedData['lineTotal'][$index],
                    $actor,
                    $poId
                );

                if ($POLinesAdd['status'] !== 'success') {
                    \Log::error('Failed to add PO line.', [
                        'index' => $index,
                        'item' => $itemCode,
                        'response' => $POLinesAdd,
                    ]);

                    return response()->json([
                        'message' => 'Failed to add PO line',
                        'error' => $POLinesAdd['error'] ?? 'Line creation error'
                    ], 500);
                }
            }

            $POSum = $this->orderService->AddPurchaseOrderSum(
                $poId
            );
            if ($POSum['status'] !== 'success') {
                \Log::error('Failed to calculate POs sum.', [
                    'po_id' => $poId,
                    'response' => $POSum,
                ]);

                return response()->json([
                    'message' => 'Failed to calculate PO sum',
                    'error' => $POSum['error'] ?? 'Sum calculation error'
                ], 500);
            }

            // Everything succeeded
            return response()->json([
                'message' => $POAdd['message'] ?? 'Order created successfully',
                'route' => route('purchaseOrder.index')
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Exception occurred while creating order.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        try {
            $order = Order::findOrFail($id); // This will throw 404 if not found
            $this->authorize('view', $order); // Authorize the order object itself

            $orderInfo = $this->orderService->fetchOrderDetails($id);
            $lineInfo = $this->orderService->fetchOrderLineDetails($id);

            //dd($orderInfo->terms_description);
            if ($request->ajax()) {
                // Return only the inner content for modal
                return view('procurement.orders.partials.show_content', compact('orderInfo', 'lineInfo'))->render();
            }
            return view('procurement.orders.show', compact('orderInfo', 'lineInfo'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Log::warning("Unauthorized access attempt to view Order ID: {$id} by user ID: " . (auth()->user()->Id ?? 'guest'));
            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Order ID {$id} not found. Exception: " . $e->getMessage());
            return redirect()->back()->with('error', 'Order not found.');
        } catch (\Exception $e) {
            \Log::error("Failed to fetch order ID {$id}. Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to fetch order.');
        }
    }

    public function relatedPO()
    {


//        $this->authorize('view', Order::query()->findOrFail($id));
//        dd($id);
        return view('procurement.orders.index');

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function linkRFQ()
    {
        try {
            $RFQ = $this->rfqService->fetchRFQ();
//            \Log::info('RFQ loaded in create():', $RFQ->toArray());
        } catch (\Exception $e) {
            \Log::error('Error fetching RFQS: ' . $e->getMessage());
            $RFQ = collect(); // fallback to empty collection
        }

        return view("procurement.orders.rfqlink", compact('RFQ'));
    }

    public function approval($id)
    {


        try {
            $order = Order::findOrFail($id); // This will throw 404 if not found
            $this->authorize('view', $order); // Authorize the order object itself

            $orderInfo = $this->orderService->fetchOrderDetails($id);
            $lineInfo = $this->orderService->fetchOrderLineDetails($id);

            return view('procurement.orders.approval', compact('orderInfo', 'lineInfo'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Log::warning("Unauthorized access attempt to view Order ID: {$id} by user ID: " . (auth()->user()->Id ?? 'guest'));
            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Order ID {$id} not found. Exception: " . $e->getMessage());
            return redirect()->back()->with('error', 'Order not found.');
        } catch (\Exception $e) {
            \Log::error("Failed to fetch order ID {$id}. Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to fetch order.');
        }

    }

    public function approve(ApproveOrderRequest $orderRequest, $id)
    {
        return $this->documentApprovalService->approve($orderRequest, $id);
    }

    public function getAwardedRFQs(): JsonResponse
    {
        try {
            $fromRfqAward = collect();
            try {
                $fromRfqAward = DB::table('t_RFQAward as a')
                    ->join('t_RFQ as r', 'a.RFQId', '=', 'r.Id')
                    ->select('r.Id', 'r.RFQNumber', 'a.SupplierId')
                    ->get();
            } catch (\Throwable $e) {
                \Log::warning('getAwardedRFQs: RFQAward join failed', ['error' => $e->getMessage()]);
            }

            $fromTender = collect();
            try {
                $fromTender = DB::table('t_TenderAwards as ta')
                    ->join('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
                    ->join('t_RFQ as r', 'r.RFQNumber', '=', 't.TenderNo')
                    ->where('ta.AwardStatus', '=', 'Approved')
                    ->select('r.Id', 'r.RFQNumber', DB::raw('ta.WinningSupplierID as SupplierId'))
                    ->get();
            } catch (\Throwable $e) {
                \Log::warning('getAwardedRFQs: TenderAwards join failed', ['error' => $e->getMessage()]);
            }

            $awarded = $fromRfqAward->concat($fromTender)->unique('Id')->values();

            // Fallback by RFQId lookup if needed
            if ($awarded->isEmpty()) {
                try {
                    $rfqIds = DB::table('t_RFQAward')->pluck('RFQId')->toArray();
                    if (!empty($rfqIds)) {
                        $rfqsBasic = DB::table('t_RFQ')->whereIn('Id', $rfqIds)->select('Id', 'RFQNumber')->get();
                        $supplierByRfq = DB::table('t_RFQAward')->pluck('SupplierId', 'RFQId');
                        $awarded = $rfqsBasic->map(function ($r) use ($supplierByRfq) {
                            $r->SupplierId = (int) ($supplierByRfq[$r->Id] ?? 0);
                            return $r;
                        })->values();
                    }
                } catch (\Throwable $e) {
                    \Log::warning('getAwardedRFQs: fallback failed', ['error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $awarded,
            ]);
        } catch (\Throwable $e) {
            \Log::error('getAwardedRFQs failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'data' => []], 200);
        }
    }

    public function fetchRFQDetails($id): JsonResponse
    {
        try {
            $RFQData = $this->rfqService->RFQTOPO($id);

            return response()->json([
                'success' => true,
                'data' => $RFQData,
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Log::warning("Unauthorized access attempt to view RFQ ID: {$id} by user ID: " . (auth()->user()->Id ?? 'guest'));

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("RFQ ID {$id} not found. Exception: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'RFQ not found.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error("Failed to fetch RFQ ID {$id}. Exception: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch RFQ.',
                'error' => $e->getMessage(), 
            ], 500);
        }
    }
public function getRFQItems($rfqId)
{
    $rfqResponse = RFQResponse::with(['items.item'])->where('RFQID', $rfqId)->first();

    if (!$rfqResponse) {
        return response()->json(['items' => []]);
    }

    $items = $rfqResponse->items->map(function ($item) {
        return [
            'itemCode' => $item->ItemCode,
            'itemName' => $item->item->ItemName ?? '',
            'itemType' => $item->item->ItemType ?? '',
            'quantity' => $item->Quantity,
            'unitPrice' => $item->UnitPrice,
        ];
    });

    return response()->json(['items' => $items]);
}

    // =====================================================
    // UNIFIED PO ORIGINATION HELPER METHODS
    // =====================================================

    /**
     * Get available approved awards for PO creation
     */
    private function getAvailableAwards()
    {
        return TenderAward::with(['tender', 'winningSupplier.thirdParty'])
            ->where('AwardStatus', 'Approved')
            ->whereDoesntHave('orders') // Not yet converted to LPO
            ->select('Id', 'TenderID', 'WinningSupplierID', 'AwardedAmount', 'AwardDate', 'ContractStatus')
            ->orderBy('AwardDate', 'desc')
            ->get()
            ->map(function ($award) {
                return [
                    'id' => $award->Id,
                    'tender_no' => $award->tender->TenderNo ?? 'N/A',
                    'title' => $award->tender->Title ?? 'N/A',
                    'supplier_name' => $award->winningSupplier->thirdParty->TradingName ?? $award->winningSupplier->thirdParty->ThirdPartyName ?? 'N/A',
                    'awarded_amount' => $award->AwardedAmount,
                    'award_date' => $award->AwardDate?->format('Y-m-d'),
                    'type' => $award->tender->TenderType ?? 'Tender',
                    'has_contract' => !empty($award->ContractStatus),
                ];
            });
    }

    /**
     * Get available active contracts for PO creation
     */
    private function getAvailableContracts()
    {
        return TenderAward::with(['tender', 'winningSupplier.thirdParty'])
            ->where('AwardStatus', 'Approved')
            ->where('ContractStatus', 'Active')
            ->whereDoesntHave('orders') // Not yet converted to LPO
            ->select('Id', 'TenderID', 'WinningSupplierID', 'ContractValue', 'ContractStartDate', 'ContractEndDate', 'ContractRef')
            ->orderBy('ContractStartDate', 'desc')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->Id,
                    'contract_ref' => $contract->ContractRef,
                    'tender_no' => $contract->tender->TenderNo ?? 'N/A',
                    'title' => $contract->tender->Title ?? 'N/A',
                    'supplier_name' => $contract->winningSupplier->thirdParty->TradingName ?? $contract->winningSupplier->thirdParty->ThirdPartyName ?? 'N/A',
                    'contract_value' => $contract->ContractValue,
                    'start_date' => $contract->ContractStartDate?->format('Y-m-d'),
                    'end_date' => $contract->ContractEndDate?->format('Y-m-d'),
                ];
            });
    }

    /**
     * Get available procurement plans for direct procurement (grouped by plan)
     */
    private function getAvailableProcurementPlans()
    {
        return PlanLineItem::with(['consolidatedProcurementPlan', 'item', 'branch', 'department'])
            ->whereHas('consolidatedProcurementPlan', function ($query) {
                $query->where('Status', 'Ap'); // Actual status in database
            })
            ->where('ProcurementMethod', 106) // Direct Purchase method (ID from t_CodeDetails)
            ->whereIn('ExecutionStatus', ['Pending', 'Approved']) // Include pending and approved items
            ->whereDoesntHave('orders') // Not yet converted to LPO
            ->select('LineItemID', 'PlanID', 'ItemID', 'MergedQty', 'EstimatedUnitCost', 'UnitOfMeasure', 'ExpectedDeliveryDate', 'BranchID', 'DepartmentID')
            ->orderBy('ExpectedDeliveryDate')
            ->get()
            ->groupBy('PlanID')
            ->map(function ($planItems, $planId) {
                $firstItem = $planItems->first();
                $totalEstimatedCost = $planItems->sum(function ($item) {
                    return $item->MergedQty * $item->EstimatedUnitCost;
                });
                
                return [
                    'plan_id' => $planId,
                    'plan_ref' => $firstItem->consolidatedProcurementPlan->ReferenceNumber ?? 'N/A',
                    'plan_title' => $firstItem->consolidatedProcurementPlan->Title ?? 'N/A',
                    'items_count' => $planItems->count(),
                    'total_estimated_cost' => $totalEstimatedCost,
                    'expected_delivery' => $firstItem->ExpectedDeliveryDate ? (is_string($firstItem->ExpectedDeliveryDate) ? $firstItem->ExpectedDeliveryDate : $firstItem->ExpectedDeliveryDate->format('Y-m-d')) : 'N/A',
                    'branch' => $firstItem->branch->Name ?? 'N/A',
                    'department' => $firstItem->department->Name ?? 'N/A',
                ];
            })
            ->values();
    }

    // =====================================================
    // AJAX ENDPOINTS FOR DYNAMIC DATA LOADING
    // =====================================================
    
    /**
     * AJAX: Get main item categories for a specific plan (ParentID is null)
     */
    public function getPlanItemCategories($planId): JsonResponse
    {
        try {
            // Get main categories that have items in the plan (through sub-categories)
            $categories = PlanLineItem::where('t_PlanLineItem.PlanID', $planId)
                ->where('t_PlanLineItem.ProcurementMethod', 106) // Direct Purchase
                ->whereIn('t_PlanLineItem.ExecutionStatus', ['Pending', 'Approved'])
                ->join('t_Items', 't_PlanLineItem.ItemID', '=', 't_Items.Id')
                ->join('t_ItemCategories as sub_cat', 't_Items.Category', '=', 'sub_cat.Id') // Sub-category where item is assigned
                ->join('t_ItemCategories as main_cat', 'sub_cat.ParentId', '=', 'main_cat.Id') // Main category (parent)
                ->whereNotNull('sub_cat.ParentId') // Ensure items are in sub-categories
                ->whereNull('main_cat.ParentId') // Ensure we get main categories only
                // ->where('main_cat.Status', 290) // Active status - removed for now
                ->select('main_cat.Id as category_id', 'main_cat.Name as category_name', 'main_cat.Description')
                ->distinct()
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->category_id,
                        'name' => $category->category_name,
                        'description' => $category->Description ?? ''
                    ];
                });

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch plan item categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch item categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Get prequalified suppliers for an item category
     */
    public function getPrequalifiedSuppliers($itemCategoryId): JsonResponse
    {
        try {
            $suppliers = DB::table('t_SupplierCategory_ItemCategory as pivot')
                ->join('t_SupplierCategories as sc', 'pivot.SupplierCategoryID', '=', 'sc.SupplierCategoryID')
                ->join('t_ThirdParty_SupplierCategory as tpsc', 'sc.SupplierCategoryID', '=', 'tpsc.supplier_category_id')
                ->join('t_ThirdParties as tp', 'tpsc.third_party_id', '=', 'tp.Id')
                ->where('pivot.ItemCategoryID', $itemCategoryId)
                ->whereNull('pivot.DeletedOn')
                ->where('sc.IsActive', 1)
                ->where('tp.Status', 'A') // Active suppliers (Status = 'A' for Active)
                ->select('tp.Id', 'tp.TradingName', 'tp.ThirdPartyName', 'sc.CategoryName as supplier_category')
                ->distinct()
                ->get()
                ->map(function ($supplier) {
                    return [
                        'id' => $supplier->Id,
                        'name' => $supplier->TradingName ?: $supplier->ThirdPartyName,
                        'address' => '', // Address field not available in t_ThirdParties
                        'supplier_category' => $supplier->supplier_category
                    ];
                });

            return response()->json([
                'success' => true,
                'suppliers' => $suppliers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch prequalified suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch prequalified suppliers.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Get items within a plan filtered by main category (items are in sub-categories)
     */
    public function getPlanItemsByCategory($planId, $mainCategoryId): JsonResponse
    {
        try {
            $planItems = PlanLineItem::with(['consolidatedProcurementPlan', 'item.category', 'branch', 'department'])
                ->where('PlanID', $planId)
                ->where('ProcurementMethod', 106) // Direct Purchase
                ->whereIn('ExecutionStatus', ['Pending', 'Approved'])
                ->whereHas('item', function ($query) use ($mainCategoryId) {
                    // Find items in sub-categories that belong to the selected main category
                    $query->whereHas('category', function ($categoryQuery) use ($mainCategoryId) {
                        $categoryQuery->where('ParentId', $mainCategoryId);
                    });
                })
                // ->whereDoesntHave('orders') // Not yet converted to LPO - temporarily disabled
                ->get()
                ->map(function ($planItem) {
                    return [
                        'id' => $planItem->LineItemID,
                        'plan_ref' => $planItem->consolidatedProcurementPlan->ReferenceNumber ?? 'N/A',
                        'item_id' => $planItem->ItemID,
                        'item_name' => $planItem->item->ItemName ?? 'N/A',
                        'item_description' => $planItem->item->ItemDescription ?? 'N/A',
                        'sub_category_name' => $planItem->item->category->Name ?? 'N/A',
                        'planned_quantity' => $planItem->MergedQty,
                        'unit_cost' => $planItem->EstimatedUnitCost,
                        'unit_of_measure' => $planItem->UnitOfMeasure,
                        'delivery_date' => $planItem->ExpectedDeliveryDate ? (is_string($planItem->ExpectedDeliveryDate) ? $planItem->ExpectedDeliveryDate : $planItem->ExpectedDeliveryDate->format('Y-m-d')) : 'N/A',
                        'branch' => $planItem->branch->Name ?? 'N/A',
                        'department' => $planItem->department->Name ?? 'N/A',
                        'total_estimated_cost' => $planItem->MergedQty * $planItem->EstimatedUnitCost
                    ];
                });

            return response()->json([
                'success' => true,
                'items' => $planItems
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch plan items by category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plan items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Get award details for PO creation
     */
    public function getAwardDetails($awardId): JsonResponse
    {
        try {
            $award = TenderAward::with(['tender.items.item', 'winningSupplier.thirdParty'])
                ->findOrFail($awardId);

            $supplierData = [
                'id' => $award->winningSupplier->Id,
                'name' => $award->winningSupplier->thirdParty->TradingName ?? $award->winningSupplier->thirdParty->ThirdPartyName ?? 'N/A',
                'address' => $award->winningSupplier->thirdParty->Address ?? '',
            ];

            $itemsData = $award->tender->items->map(function ($tenderItem) {
                return [
                    'item_id' => $tenderItem->ItemID,
                    'item_name' => $tenderItem->item->ItemName ?? $tenderItem->ManualItemDescription,
                    'description' => $tenderItem->item->ItemDescription ?? $tenderItem->ManualItemDescription,
                    'quantity' => $tenderItem->QtyToTender,
                    'unit_price' => 0, // To be filled by user
                    'item_type_id' => $tenderItem->item->ItemTypeID ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'supplier' => $supplierData,
                'items' => $itemsData,
                'award' => [
                    'tender_no' => $award->tender->TenderNo,
                    'title' => $award->tender->Title,
                    'awarded_amount' => $award->AwardedAmount,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch award details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch award details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Get contract details for PO creation
     */
    public function getContractDetails($contractId): JsonResponse
    {
        try {
            $contract = TenderAward::with(['tender.items.item', 'winningSupplier.thirdParty'])
                ->where('ContractStatus', 'Active')
                ->findOrFail($contractId);

            $supplierData = [
                'id' => $contract->winningSupplier->Id,
                'name' => $contract->winningSupplier->thirdParty->TradingName ?? $contract->winningSupplier->thirdParty->ThirdPartyName ?? 'N/A',
                'address' => $contract->winningSupplier->thirdParty->Address ?? '',
            ];

            $itemsData = $contract->tender->items->map(function ($tenderItem) {
                return [
                    'item_id' => $tenderItem->ItemID,
                    'item_name' => $tenderItem->item->ItemName ?? $tenderItem->ManualItemDescription,
                    'description' => $tenderItem->item->ItemDescription ?? $tenderItem->ManualItemDescription,
                    'quantity' => $tenderItem->QtyToTender,
                    'unit_price' => 0, // To be filled by user based on contract terms
                    'item_type_id' => $tenderItem->item->ItemTypeID ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'supplier' => $supplierData,
                'items' => $itemsData,
                'contract' => [
                    'contract_ref' => $contract->ContractRef,
                    'tender_no' => $contract->tender->TenderNo,
                    'title' => $contract->tender->Title,
                    'contract_value' => $contract->ContractValue,
                    'delivery_terms' => $contract->DeliveryTerms,
                    'payment_terms' => $contract->PaymentTerms,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch contract details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contract details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Get procurement plan item details for direct procurement
     */
    public function getPlanItemDetails($planItemId): JsonResponse
    {
        try {
            $planItem = PlanLineItem::with(['consolidatedProcurementPlan', 'item', 'branch', 'department'])
                ->findOrFail($planItemId);

            $itemData = [
                'item_id' => $planItem->ItemID,
                'item_name' => $planItem->item->ItemName ?? 'N/A',
                'description' => $planItem->item->ItemDescription ?? 'N/A',
                'quantity' => $planItem->MergedQty,
                'estimated_unit_cost' => $planItem->EstimatedUnitCost,
                'unit_of_measure' => $planItem->UnitOfMeasure,
                'item_type_id' => $planItem->item->ItemTypeID ?? null,
            ];

            return response()->json([
                'success' => true,
                'item' => $itemData,
                'plan' => [
                    'plan_ref' => $planItem->consolidatedProcurementPlan->ReferenceNumber,
                    'title' => $planItem->consolidatedProcurementPlan->Title,
                    'delivery_date' => $planItem->ExpectedDeliveryDate ? (is_string($planItem->ExpectedDeliveryDate) ? $planItem->ExpectedDeliveryDate : $planItem->ExpectedDeliveryDate->format('Y-m-d')) : 'N/A',
                    'branch' => $planItem->branch->Name ?? 'N/A',
                    'department' => $planItem->department->Name ?? 'N/A',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch plan item details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plan item details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // =====================================================
    // UNIFIED ORDER STORAGE METHOD
    // =====================================================

    /**
     * Store unified order based on origination type
     */
    private function storeUnifiedOrder(Request $request, array $validatedData): JsonResponse
    {
        try {
            Log::info('Starting unified order creation', [
                'origination_type' => $request->input('origination_type'),
                'supplier' => $request->input('supplier'),
                'user_id' => auth()->id()
            ]);

            $actor = $request->user();
            if (!$actor) {
                Log::error('No authenticated user found for unified order creation');
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            
            // Additional debugging for user ID
            Log::info('Authenticated user for unified order', [
                'user_id' => $actor->id,
                'user_name' => $actor->name ?? 'N/A',
                'user_email' => $actor->email ?? 'N/A',
                'actor_id_type' => gettype($actor->id),
                'actor_id_value' => $actor->id
            ]);

            $originationType = $request->input('origination_type');
            
            // Set origination-specific data
            $originationData = $this->prepareOriginationData($request, $originationType);
            
            // Get user ID with multiple fallbacks and extensive debugging
            $userId = $actor->id ?? auth()->id() ?? 1; // Fallback to user ID 1 if auth fails
            
            Log::info('User ID resolution debug', [
                'actor_id' => $actor->id,
                'auth_id' => auth()->id(),
                'final_userId' => $userId,
                'userId_type' => gettype($userId),
                'actor_object' => $actor ? get_class($actor) : null,
                'auth_user_object' => auth()->user() ? get_class(auth()->user()) : null
            ]);
            
            if (!$userId) {
                Log::error('Unable to determine user ID for order creation', [
                    'actor' => $actor,
                    'auth_id' => auth()->id(),
                    'auth_user' => auth()->user()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication error: Unable to determine user ID'
                ], 401);
            }
            
            // Force userId to integer to avoid any type issues
            $userId = (int) $userId;
            
            // Create the order with origination information
            $orderData = [
                'OrderNo' => $request->input('LPONo', 'LPO-' . uniqid()),
                'OrderDate' => $request->input('pODate'),
                'Terms' => $request->input('terms'),
                'Priority' => $request->input('priority', 'Medium'),
                'AccountID' => $request->input('supplier'),
                'Status' => 'draft',
                'OriginationType' => $originationType,
                'OriginationRef' => $originationData['ref'] ?? null,
                'ContractRef' => $originationType === 'contract' ? $request->input('contract_id') : null,
                'AwardRef' => $originationType === 'award' ? $request->input('award_id') : null,
                'PlanRef' => $originationType === 'direct' ? $request->input('plan_item_id') : null,
                'ExtOrdNum' => $originationType === 'rfq' ? $request->input('rfq_id') : null,
                'Notes' => $request->input('notes'),
                'DeliveryTerms' => $request->input('delivery_terms'),
                'CreatedBy' => $userId,
                'ModifiedBy' => $userId,
            ];
            
            Log::info('Order data prepared', ['orderData' => $orderData]);
            
            // Calculate total from line items
            $orderData['TotalAmount'] = $this->calculateOrderTotal($request);
            
            Log::info('Final order data before database insert', [
                'orderData' => $orderData,
                'CreatedBy_value' => $orderData['CreatedBy'],
                'ModifiedBy_value' => $orderData['ModifiedBy']
            ]);
            
            // Create the order
            try {
                $order = Order::create($orderData);
                Log::info('Order created successfully', ['order_id' => $order->Id]);
            } catch (\Exception $e) {
                Log::error('Failed to create order in database', [
                    'error' => $e->getMessage(),
                    'orderData' => $orderData,
                    'sql_error' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
                ]);
                throw $e; // Re-throw to be caught by outer try-catch
            }
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order',
                ], 500);
            }
            
            // Create order lines
            $this->createOrderLines($request, $order->Id);
            
            // Update origination source status if needed
            $this->updateOriginationSourceStatus($originationType, $originationData['ref'] ?? null);
            
            Log::info('Unified order created successfully', [
                'order_id' => $order->Id,
                'origination_type' => $originationType,
                'user_id' => $actor->id,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'route' => route('purchaseOrder.index'), // Add redirect route
                'data' => [
                    'order_id' => $order->Id,
                    'order_no' => $order->OrderNo,
                    'origination_type' => $originationType,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Unified order creation failed: ' . $e->getMessage(), [
                'input' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Prepare origination-specific data
     */
    private function prepareOriginationData(Request $request, string $originationType): array
    {
        switch ($originationType) {
            case 'rfq':
                return ['ref' => $request->input('rfq_id')];
            case 'award':
                return ['ref' => $request->input('award_id')];
            case 'contract':
                return ['ref' => $request->input('contract_id')];
            case 'direct':
                return ['ref' => $request->input('plan_item_id')];
            default:
                return [];
        }
    }
    
    /**
     * Calculate total amount from line items
     */
    private function calculateOrderTotal(Request $request): float
    {
        $total = 0.0;
        
        $quantities = $request->input('quantity', []);
        $unitPrices = $request->input('unitPrice', []);
        $taxes = $request->input('tax', []);
        $discounts = $request->input('discount', []);
        
        foreach ($quantities as $index => $quantity) {
            $quantity = floatval($quantity);
            $unitPrice = floatval($unitPrices[$index] ?? 0);
            $tax = floatval($taxes[$index] ?? 0);
            $discount = floatval($discounts[$index] ?? 0);
            
            if ($quantity > 0 && $unitPrice > 0) {
                $lineTotal = $quantity * $unitPrice;
                
                // Apply discount
                if ($discount > 0) {
                    $lineTotal -= $lineTotal * ($discount / 100);
                }
                
                // Apply tax
                if ($tax > 0) {
                    $lineTotal += $lineTotal * ($tax / 100);
                }
                
                $total += $lineTotal;
            }
        }
        
        return $total;
    }
    
    /**
     * Create order line items
     */
    private function createOrderLines(Request $request, int $orderId): void
    {
        $itemCodes = $request->input('itemCode', []);
        $descriptions = $request->input('itemDescription', []);
        $quantities = $request->input('quantity', []);
        $unitPrices = $request->input('unitPrice', []);
        $taxes = $request->input('tax', []);
        $discounts = $request->input('discount', []);
        $lineTotals = $request->input('lineTotal', []);
        
        foreach ($itemCodes as $index => $itemCode) {
            if (empty($quantities[$index]) || empty($unitPrices[$index])) {
                continue;
            }
            
            $orderLineData = [
                'iOrderID' => $orderId,
                'cDescription' => $descriptions[$index] ?? '',
                'fQuantity' => floatval($quantities[$index]),
                'fUnitPriceExcl' => floatval($unitPrices[$index]),
                'TaxPercentage' => floatval($taxes[$index] ?? 0),
                'DiscountPercentage' => floatval($discounts[$index] ?? 0),
                'LineTotal' => floatval($lineTotals[$index] ?? 0),
                'iStockCodeID' => !empty($itemCode) ? intval($itemCode) : null,
                'cLineNotes' => '',
                'CreatedBy' => auth()->id(),
                'ModifiedBy' => auth()->id(),
            ];
            
            OrderLines::create($orderLineData);
        }
    }
    
    /**
     * Update origination source status
     */
    private function updateOriginationSourceStatus(string $originationType, $sourceRef): void
    {
        if (!$sourceRef) return;
        
        try {
            switch ($originationType) {
                case 'award':
                case 'contract':
                    // Mark award/contract as having an order created
                    TenderAward::where('Id', $sourceRef)
                        ->update(['ModifiedBy' => auth()->id(), 'ModifiedOn' => now()]);
                    break;
                    
                case 'direct':
                    // Mark plan item as in execution
                    PlanLineItem::where('LineItemID', $sourceRef)
                        ->update([
                            'ExecutionStatus' => 'In Progress', 
                            'ModifiedBy' => auth()->id(),
                            'ModifiedOn' => now()
                        ]);
                    break;
                    
                // RFQ doesn't need status update as multiple POs can be created from one RFQ
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update origination source status', [
                'type' => $originationType,
                'ref' => $sourceRef,
                'error' => $e->getMessage()
            ]);
        }
    }


}
