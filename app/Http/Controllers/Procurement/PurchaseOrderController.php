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
use App\Models\Procurement\RFQResponse;
use App\Services\ThirdParty\SupplierService;
use Illuminate\Support\Facades\Schema;
use App\Models\ThirdParty\SupplierCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon\Carbon;
use App\Models\Core\CodeDetail;

class PurchaseOrderController extends Controller
{
    public function __construct(protected ItemService $itemService, protected SupplierService $supplierService, protected OrderService $orderService, protected RFQService $rfqService, protected DocumentApprovalService $documentApprovalService)
    {
        $this->middleware('ajax')->except([
            'index',
            'create',
            'store', // Allow regular form submission for store
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
            'getAwardedTenders',
            'getTenderItems',
            'getContractItems',
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
            Log::info('Suppliers data:', $suppliers->toArray());
            return response()->json([
                'success' => true,
                'data' => $suppliers,
            ]);} 
        catch(\Exception $e){
            Log::error('Error fetching suppliers: ' . $e->getMessage());

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
            $suppliers = $this->supplierService->getSuppliers();
            // Fetch payment terms from t_CodeDetails (robust to casing/whitespace/pluralization)
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

            // RFQs awarded via RFQAward
            $awardedFromRFQAward = collect();
            try {
                $awardedFromRFQAward = DB::table('t_RFQAward as a')
                    ->join('t_RFQ as r', 'a.RFQId', '=', 'r.Id')
                    ->leftJoin('t_Suppliers as s', 's.Id', '=', 'a.SupplierId')
                    ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
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
                \Log::warning('Skipping RFQAward join for awarded RFQs', ['error' => $e->getMessage()]);
                $awardedFromRFQAward = collect();
            }

            // Only list RFQs awarded via t_RFQAward (source of truth)
            $awardedRfqs = $awardedFromRFQAward->unique('Id')->values();

            // Fallback if needed
            // No fallback to non-awarded RFQs; dropdown must show only awarded RFQs

            $convertedRFQIds = DB::table('t_Orders')
                ->where('SourceType', 'RFQ')
                ->whereNotNull('SourceId')
                ->pluck('SourceId')
                ->toArray();

            // Tenders that have awards but NO existing contracts (for Tender source dropdown)
            $awardedTenders = collect();
            try {
                $awardedTenders = DB::table('t_TenderAwards as ta')
                    ->join('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
                    ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
                    ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                    ->where('ta.AwardStatus', 'Approved') // Only approved awards
                    ->where(function ($q) {
                        // Only include tenders that DON'T have contracts
                        $q->whereNull('ta.ContractStatus')
                          ->orWhere('ta.ContractStatus', '')
                          ->orWhere('ta.ContractStatus', 'No Contract Required');
                    })
                    ->select(
                        't.Id',
                        't.TenderNo',
                        DB::raw('ta.WinningSupplierID as SupplierId'),
                        DB::raw('tp.Id as ThirdPartyId'),
                        DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                        DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                    )
                    ->get();
                
                \Log::info('Filtered awarded tenders without contracts', ['count' => $awardedTenders->count()]);
            } catch (\Throwable $e) {
                \Log::warning('Skipping TenderAwards join for awarded tenders', ['error' => $e->getMessage()]);
                $awardedTenders = collect();
            }

            $convertedTenderIds = DB::table('t_Orders')
                ->where('SourceType', 'TENDER')
                ->whereNotNull('SourceId')
                ->pluck('SourceId')
                ->toArray();

            // Active contracts for Contract-based source selection (Approved or Executed)
            $contracts = DB::table('t_TenderAwards as ta')
                ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
                ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                ->whereIn('ta.ContractStatus', ['Approved', 'Executed']) // Include both Approved and Executed contracts
                ->whereNotNull('ta.ContractRef') // Must have a contract reference
                ->where('ta.ContractRef', '!=', '') // Contract reference cannot be empty
                ->orderByDesc('ta.ContractApprovedOn')
                ->select(
                    'ta.Id as Id',
                    'ta.ContractRef as ContractRef',
                    'ta.WinningSupplierID as SupplierId',
                    DB::raw('tp.Id as ThirdPartyId'),
                    DB::raw("tp.TradingName as SupplierName"),
                    DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                    'ta.ContractStatus' // Include status for display
                )
                ->get();
                
            \Log::info('Active contracts loaded for LPO', ['count' => $contracts->count()]);

            // Optional contract prefill support: if contractId is present, pre-select reference and supplier
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
                        // Ensure RFQ references contain the contract ref so the existing dev dropdown can select it
                        $uniqueRfqs = collect($uniqueRfqs);
                        $exists = $uniqueRfqs->contains(function ($r) use ($contractRow) {
                            return ($r->RFQNumber ?? null) === ($contractRow->ContractRef ?? null);
                        });
                        if (!$exists) {
                            $uniqueRfqs = $uniqueRfqs->prepend((object) ['RFQNumber' => $contractRow->ContractRef]);
                        }

                        // Inject synthetic rfqResponse entry so supplier filtering works
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
                    \Log::warning('Contract prefill failed', ['error' => $e->getMessage()]);
                }
            }

            $sourceType = $prefillContract ? 'CONTRACT' : 'RFQ';

            return view('procurement.orders.create', [
                'itemTypes' => $itemTypes ?? [],
                'allItems' => $allItems ?? collect(),
                'rfqs' => $uniqueRfqs ?? [],
                'rfqResponses' => $rfqResponses ?? [],
                'suppliers' => $suppliers ?? [],
                'paymentTerms' => $paymentTerms ?? [], // Pass payment terms to view
                'prefillContract' => $prefillContract,
                'sourceType' => $sourceType,
                'contracts' => $contracts ?? collect(),
                'awardedRfqs' => $awardedRfqs ?? collect(),
                'convertedRFQIds' => $convertedRFQIds ?? [],
                'awardedTenders' => $awardedTenders ?? collect(),
                'convertedTenderIds' => $convertedTenderIds ?? [],
            ]);
        } catch (\Exception $e) {
            \Log::error('Data fetch failed: ' . $e->getMessage());
            // Ensure dev-expected vars exist even on failure
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
            ])->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseOrderRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Derive reference number safely based on source selection when not provided
            $referenceNumber = $validatedData['refNo'] ?? null;
            if (empty($referenceNumber)) {
                $sourceType = (string) $request->input('SourceType', '');
                $sourceId = (int) $request->input('SourceId', 0);
                try {
                    if ($sourceType === 'RFQ' && $sourceId > 0) {
                        $referenceNumber = (string) (DB::table('t_RFQ')->where('Id', $sourceId)->value('RFQNumber') ?? '');
                    } elseif ($sourceType === 'TENDER' && $sourceId > 0) {
                        $referenceNumber = (string) (DB::table('t_Tenders')->where('Id', $sourceId)->value('TenderNo') ?? '');
                    } elseif ($sourceType === 'CONTRACT' && $sourceId > 0) {
                        $referenceNumber = (string) (DB::table('t_TenderAwards')->where('Id', $sourceId)->value('ContractRef') ?? '');
                    } else {
                        $referenceNumber = $referenceNumber ?? '';
                    }
                } catch (\Throwable $e) {
                    $referenceNumber = $referenceNumber ?? '';
                }
            }

            $actor = $request->user();
            if (!$actor) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthorized'], 401);
                }
                return redirect()->back()->with('error', 'Unauthorized access.');
            }

            // Ensure terms is a valid ID from t_CodeDetails
            if (!DB::table('t_CodeDetails')->where('ID', $validatedData['terms'])->where('CodeID', 'PaymentTerm')->exists()) {
                Log::error('Invalid payment term ID provided.', [
                    'terms' => $validatedData['terms'],
                    'user_id' => $actor->Id ?? null,
                ]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Invalid payment term selected.',
                        'error' => 'The selected payment term does not exist.'
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', 'Invalid payment term selected.')
                    ->withInput();
            }

            // Pass the terms ID (from t_CodeDetails.ID) to addPO
            $POAdd = $this->orderService->addPO(
                $validatedData['supplier'],
                $validatedData['pODate'],
                $referenceNumber,
                $validatedData['priority'] ?? 'Medium',
                $validatedData['terms'], // This is the ID from t_CodeDetails
                $actor
            );

            if ($POAdd['status'] !== 'success') {
                Log::error('Failed to create PO.', [
                    'input' => $validatedData,
                    'user_id' => $actor->Id ?? null,
                    'service_response' => $POAdd,
                ]);

                $errorMessage = $POAdd['message'] ?? 'Failed to create purchase order';
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $errorMessage,
                        'error' => $POAdd['error'] ?? 'Unknown error'
                    ], 500);
                }
                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->withInput();
            }

            $poId = $POAdd['po_id'] ?? null;

            if (!$poId) {
                Log::error('PO created but no ID returned.', [
                    'response' => $POAdd
                ]);

                $errorMessage = 'Purchase order created but no ID returned.';
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $errorMessage,
                        'error' => 'Missing PO ID'
                    ], 500);
                }
                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->withInput();
            }

            // Process each PO line
            foreach ($validatedData['itemCode'] as $index => $itemCode) {
                $POLinesAdd = $this->orderService->addPOLines(
                    $itemCode,
                    $validatedData['quantity'][$index] ?? 0,
                    $validatedData['unitPrice'][$index] ?? 0,
                    $validatedData['tax'][$index] ?? 0,
                    $validatedData['discount'][$index] ?? 0,
                    $validatedData['lineTotal'][$index] ?? 0,
                    $actor,
                    $poId
                );

                if ($POLinesAdd['status'] !== 'success') {
                    Log::error('Failed to add PO line.', [
                        'index' => $index,
                        'item' => $itemCode,
                        'response' => $POLinesAdd,
                    ]);

                    $errorMessage = 'Failed to add PO line ' . ($index + 1);
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => $errorMessage,
                            'error' => $POLinesAdd['error'] ?? 'Line creation error'
                        ], 500);
                    }
                    return redirect()->back()
                        ->with('error', $errorMessage)
                        ->withInput();
                }
            }

            $POSum = $this->orderService->AddPurchaseOrderSum(
                $poId
            );
            if ($POSum['status'] !== 'success') {
                Log::error('Failed to calculate POs sum.', [
                    'po_id' => $poId,
                    'response' => $POSum,
                ]);

                $errorMessage = 'Failed to calculate PO totals';
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $errorMessage,
                        'error' => $POSum['error'] ?? 'Sum calculation error'
                    ], 500);
                }
                return redirect()->back()
                    ->with('error', $errorMessage)
                    ->withInput();
            }

            // Everything succeeded
            $successMessage = $POAdd['message'] ?? 'Purchase order created successfully';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $successMessage,
                    'route' => route('purchaseOrder.index')
                ], 200);
            }
            
            return redirect()->route('purchaseOrder.index')
                ->with('success', $successMessage);

        } catch (\Throwable $e) {
            Log::error('Exception occurred while creating order.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMessage = 'Failed to create purchase order: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to create order',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
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
    try {
        $supplierId = (int) request()->query('supplierId'); // ThirdPartyID if provided
        $supplierLegacyId = (int) request()->query('supplierLegacyId'); // t_Suppliers.Id (fallback)

        // Resolve ThirdPartyID from legacy supplier id if not provided directly
        if ($supplierId <= 0 && $supplierLegacyId > 0) {
            try {
                $resolved = DB::table('t_Suppliers')->where('Id', $supplierLegacyId)->value('ThirdPartyID');
                $supplierId = (int) ($resolved ?? 0);
            } catch (\Throwable $e) {
                $supplierId = 0;
            }
        }

        // Pull response items for this RFQ (optionally filtered by ThirdParty supplier), and map to catalog items by name
        $items = DB::table('t_ResponseItems as ri')
            ->join('t_RFQResponse as rr', 'ri.RfqResponseId', '=', 'rr.Id')
            ->where('rr.RFQId', (int) $rfqId)
            ->when($supplierId > 0, function ($q) use ($supplierId) {
                // Support both models:
                // 1) rr.SupplierId stores t_ThirdParties.Id (current)
                // 2) rr.SupplierId stores t_Suppliers.Id (legacy)
                $q->where(function ($qq) use ($supplierId) {
                    $qq->where('rr.SupplierId', $supplierId)
                        ->orWhereIn('rr.SupplierId', function ($sub) use ($supplierId) {
                            $sub->from('t_Suppliers')->where('ThirdPartyID', $supplierId)->select('Id');
                        });
                });
            })
            ->leftJoin('t_Items as it', 'it.ItemName', '=', 'ri.ItemName')
            ->selectRaw("COALESCE(it.Id, 0) as itemCode, COALESCE(it.ItemName, ri.ItemName) as itemName, COALESCE(it.ItemType, '') as itemType, ri.Quantity as quantity, ri.QuotedPrice as unitPrice")
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


    public function getAwardedRFQs(): JsonResponse
    {
        try {
            $awarded = DB::table('t_RFQAward as a')
                ->join('t_RFQ as r', 'a.RFQId', '=', 'r.Id')
                ->leftJoin('t_Suppliers as s', 's.Id', '=', 'a.SupplierId')
                ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                ->select(
                    'r.Id',
                    'r.RFQNumber',
                    'a.SupplierId',
                    DB::raw('tp.Id as ThirdPartyId'),
                    DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                    DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                )
                ->get();

            // No fallback to non-awarded RFQs

            return response()->json([
                'success' => true,
                'data' => $awarded,
            ]);
        } catch (\Throwable $e) {
            \Log::error('getAwardedRFQs failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'data' => []], 200);
        }
    }

    public function getAwardedTenders(): JsonResponse
    {
        try {
            // Only return tenders that are awarded but DON'T have existing contracts
            $rows = DB::table('t_TenderAwards as ta')
                ->join('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
                ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
                ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                ->where('ta.AwardStatus', 'Approved') // Only approved awards
                ->where(function ($q) {
                    // Only include tenders that DON'T have contracts
                    $q->whereNull('ta.ContractStatus')
                      ->orWhere('ta.ContractStatus', '')
                      ->orWhere('ta.ContractStatus', 'No Contract Required');
                })
                ->select(
                    't.Id',
                    't.TenderNo',
                    DB::raw('ta.WinningSupplierID as SupplierId'),
                    DB::raw('tp.Id as ThirdPartyId'),
                    DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                    DB::raw("COALESCE(tp.PhysicalAddress, '') as Address")
                )
                ->get();
                
            \Log::info('AJAX: Filtered awarded tenders without contracts', ['count' => $rows->count()]);
            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            \Log::error('getAwardedTenders failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'data' => []], 200);
        }
    }

    public function getTenderItems($tenderId): JsonResponse
    {
        try {
            // Items defined at tender level
            $items = DB::table('t_TenderItems as ti')
                ->leftJoin('t_Items as it', 'it.Id', '=', 'ti.ItemID')
                ->where('ti.TenderID', (int) $tenderId)
                ->selectRaw("COALESCE(it.Id, 0) as itemCode, COALESCE(it.ItemName, ti.ManualItemDescription) as itemName, COALESCE(it.ItemType, '') as itemType, COALESCE(ti.QtyToTender, ti.PlannedQty) as quantity, COALESCE(it.ItemPrice, 0) as unitPrice")
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
            \Log::error('Failed to fetch Tender items', ['tenderId' => $tenderId, 'error' => $e->getMessage()]);
            return response()->json(['items' => []], 200);
        }
    }

    public function getContractItems($contractId): JsonResponse
    {
        try {
            // Get contract details
            $contract = DB::table('t_TenderAwards as ta')
                ->leftJoin('t_Tenders as t', 'ta.TenderID', '=', 't.Id')
                ->where('ta.Id', (int) $contractId)
                ->whereIn('ta.ContractStatus', ['Approved', 'Executed'])
                ->select('ta.Id', 'ta.TenderID', 'ta.ContractRef')
                ->first();

            if (!$contract) {
                return response()->json(['items' => []], 404);
            }

            // Get tender items for the contract's tender with item details
            $items = DB::table('t_TenderItems as ti')
                ->leftJoin('t_Items as it', 'it.Id', '=', 'ti.ItemID')
                ->leftJoin('t_ItemTypes as itype', 'it.ItemType', '=', 'itype.Id')
                ->leftJoin('t_ItemCategories as ic', 'it.Category', '=', 'ic.Id')
                ->where('ti.TenderID', (int) $contract->TenderID)
                ->select([
                    DB::raw('COALESCE(it.Id, 0) as itemCode'),
                    DB::raw('COALESCE(it.ItemName, ti.ManualItemDescription) as itemName'),
                    DB::raw('COALESCE(it.ItemDescription, ti.ManualItemDescription) as description'),
                    DB::raw('COALESCE(itype.TypeName, \'\') as itemType'),
                    DB::raw('COALESCE(ic.Name, \'\') as categoryName'),
                    DB::raw('COALESCE(ti.QtyToTender, ti.PlannedQty, 1) as quantity'),
                    DB::raw('COALESCE(it.ItemPrice, 0) as unitPrice')
                ])
                ->get()
                ->map(function ($row) {
                    return [
                        'itemCode' => (int) $row->itemCode,
                        'itemName' => $row->itemName,
                        'description' => $row->description ?: $row->itemName,
                        'itemType' => $row->itemType,
                        'categoryName' => $row->categoryName,
                        'quantity' => (float) $row->quantity,
                        'unitPrice' => (float) $row->unitPrice,
                    ];
                });

            \Log::info('Contract tender items loaded', [
                'contractId' => $contractId,
                'contractRef' => $contract->ContractRef,
                'tenderID' => $contract->TenderID,
                'itemCount' => $items->count()
            ]);

            return response()->json([
                'items' => $items,
                'availableItems' => $items // Also provide items for dropdown population
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to fetch Contract tender items', ['contractId' => $contractId, 'error' => $e->getMessage()]);
            return response()->json(['items' => [], 'availableItems' => []], 200);
        }
    }


}
