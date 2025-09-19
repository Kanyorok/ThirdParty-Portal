<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Http\Requests\Orders\PurchaseOrderRequest;
use App\Models\Auth\User;
use App\Models\Procurement\Order;
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
        try {
            $itemTypes = $this->itemService->getTypes();
            $rfqResponses = $this->rfqService->fetchRFQ();
            $uniqueRfqs = collect($rfqResponses)->unique('RFQNumber')->values();
            $suppliers = $this->supplierService->getSuppliers();
            // Fetch payment terms from t_CodeDetails
            $paymentTerms = CodeDetail::where('CodeID', 'PaymentTerm')->get(['ID', 'Description']); 

            // RFQs that have awards and are eligible for conversion (t_RFQAward)
            $awardedFromRFQAward = collect();
            try {
                $awardedFromRFQAward = DB::table('t_RFQAward as a')
                    ->join('t_RFQ as r', 'a.RFQId', '=', 'r.Id')
                    ->select('r.Id', 'r.RFQNumber', 'a.SupplierId')
                    ->get();
            } catch (\Throwable $e) {
                \Log::warning('Skipping RFQAward join for awarded RFQs', ['error' => $e->getMessage()]);
                $awardedFromRFQAward = collect();
            }

            // Also include RFQs awarded via TenderAwards (where TenderNo maps to RFQNumber)
            $awardedFromTender = collect();
            try {
                $awardedFromTender = DB::table('t_TenderAwards as ta')
                    ->join('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
                    ->join('t_RFQ as r', 'r.RFQNumber', '=', 't.TenderNo')
                    ->where('ta.AwardStatus', '=', 'Approved')
                    ->select('r.Id', 'r.RFQNumber', DB::raw('ta.WinningSupplierID as SupplierId'))
                    ->get();
            } catch (\Throwable $e) {
                \Log::warning('Skipping TenderAwards join for awarded RFQs', ['error' => $e->getMessage()]);
                $awardedFromTender = collect();
            }

            $awardedRfqs = $awardedFromRFQAward->concat($awardedFromTender)->unique('Id')->values();

            // Fallback: if still empty but t_RFQAward has rows, fetch plainly by RFQId list
            if ($awardedRfqs->isEmpty()) {
                try {
                    $rfqIds = DB::table('t_RFQAward')->pluck('RFQId')->toArray();
                    if (!empty($rfqIds)) {
                        $rfqsBasic = DB::table('t_RFQ')->whereIn('Id', $rfqIds)->select('Id', 'RFQNumber')->get();
                        $supplierByRfq = DB::table('t_RFQAward')->pluck('SupplierId', 'RFQId');
                        $awardedRfqs = $rfqsBasic->map(function ($r) use ($supplierByRfq) {
                            $r->SupplierId = (int) ($supplierByRfq[$r->Id] ?? 0);
                            return $r;
                        })->values();
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Fallback fetch for awarded RFQs failed', ['error' => $e->getMessage()]);
                }
            }

            $convertedRFQIds = DB::table('t_Orders')
                ->where('SourceType', 'RFQ')
                ->whereNotNull('SourceId')
                ->pluck('SourceId')
                ->toArray();

            return view('procurement.orders.create', [
                'itemTypes' => $itemTypes ?? [],
                'rfqs' => $uniqueRfqs ?? [],
                'rfqResponses' => $rfqResponses ?? [],
                'suppliers' => $suppliers ?? [],
                'paymentTerms' => $paymentTerms ?? [], // Pass payment terms to view
                'awardedRfqs' => $awardedRfqs ?? [],
                'convertedRFQIds' => $convertedRFQIds ?? [],
            ]);
        } catch (\Exception $e) {
            \Log::error('Data fetch failed: ' . $e->getMessage());
            return view('procurement.orders.create', [
                'suppliers' => [],
                'itemTypes' => [],
                'rfqs' => [],
                'rfqResponses' => [],
                'paymentTerms' => [],
                'awardedRfqs' => [],
                'convertedRFQIds' => [],
            ])->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseOrderRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

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
public function getRFQItems($rfqId): JsonResponse
{
    try {
        $supplierId = (int) request()->query('supplierId');

        // Pull response items for this RFQ (optionally filtered by supplier), and map to catalog items by name
        $items = DB::table('t_ResponseItems as ri')
            ->join('t_RFQResponse as rr', 'ri.RfqResponseId', '=', 'rr.Id')
            ->where('rr.RFQId', (int) $rfqId)
            ->when($supplierId > 0, function ($q) use ($supplierId) {
                $q->where('rr.SupplierId', $supplierId);
            })
            ->leftJoin('t_Items as it', 'it.ItemName', '=', 'ri.ItemName')
            ->selectRaw('COALESCE(it.Id, 0) as itemCode, COALESCE(it.ItemName, ri.ItemName) as itemName, COALESCE(it.ItemType, \'\') as itemType, ri.Quantity as quantity, ri.QuotedPrice as unitPrice')
            ->get()
            ->map(function ($row) {
                return [
                    'itemCode' => (int) $row->itemCode,
                    'itemName' => $row->itemName,
                    'itemType' => $row->itemType,
                    'quantity' => (float) $row->quantity,
                    'unitPrice' => (float) $row->unitPrice,
                ];
            });

        return response()->json(['items' => $items]);
    } catch (\Throwable $e) {
        \Log::error('Failed to fetch RFQ items', ['rfqId' => $rfqId, 'error' => $e->getMessage()]);
        return response()->json(['items' => []], 200);
    }
}

public function prequalifiedSuppliersByCategory($categoryId): JsonResponse
{
    try {
        $categoryId = (int) $categoryId;

        // Build list: selected category + its immediate children
        $categoryIds = collect([$categoryId]);
        try {
            $children = DB::table('t_ItemCategories')->where('ParentId', $categoryId)->pluck('Id');
            $categoryIds = $categoryIds->concat($children)->unique()->values();
        } catch (\Throwable $e) {}

        // Map ItemCategoryIDs to SupplierCategoryIDs via pivot
        $supplierCategoryIds = collect();
        try {
            $supplierCategoryIds = DB::table('t_SupplierCategory_ItemCategory')
                ->whereIn('ItemCategoryID', $categoryIds)
                ->whereNull('DeletedOn')
                ->pluck('SupplierCategoryID');
        } catch (\Throwable $e) {}

        // Third-party ids from t_ThirdParty_SupplierCategory for those SupplierCategoryIDs
        $thirdPartyIds = collect();
        try {
            $thirdPartyIds = DB::table('t_ThirdParty_SupplierCategory')
                ->whereIn('SupplierCategoryID', $supplierCategoryIds)
                ->pluck('ThirdPartyID');
        } catch (\Throwable $e) {}

        // Candidates from pivot mapping
        $fromPivot = collect();
        if ($thirdPartyIds->isNotEmpty()) {
            $fromPivot = DB::table('t_ThirdParties as tp')
                ->leftJoin('t_Suppliers as s', 's.ThirdPartyID', '=', 'tp.Id')
                ->whereIn('tp.Id', $thirdPartyIds)
                ->selectRaw('COALESCE(s.Id, 0) as SupplierId, COALESCE(tp.ThirdPartyName, tp.TradingName) as SupplierName, COALESCE(tp.Address, '\'\') as Address')
                ->orderBy('SupplierName')
                ->get();
        }

        // Also include suppliers directly mapped by CategoryId in t_Suppliers
        $fromDirect = collect();
        try {
            $fromDirect = DB::table('t_Suppliers as s')
                ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                ->whereIn('s.CategoryId', $categoryIds)
                ->selectRaw('s.Id as SupplierId, COALESCE(tp.ThirdPartyName, tp.TradingName, s.SupplierName) as SupplierName, COALESCE(tp.Address, '\'\') as Address')
                ->orderBy('SupplierName')
                ->get();
        } catch (\Throwable $e) {}

        $data = $fromPivot->concat($fromDirect)
            ->unique(function ($row) { return ($row->SupplierId ?: 0) . '|' . ($row->SupplierName ?? ''); })
            ->values();

        return response()->json(['success' => true, 'data' => $data]);
    } catch (\Throwable $e) {
        \Log::error('Failed to fetch prequalified suppliers by category', ['categoryId' => $categoryId, 'error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch prequalified suppliers.',
        ], 200);
    }
}


}
