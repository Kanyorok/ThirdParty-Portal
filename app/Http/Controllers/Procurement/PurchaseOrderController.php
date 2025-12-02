<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Http\Requests\Orders\PurchaseOrderRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\Order;
use App\Services\Core\DocumentApprovalService;
use App\Services\Procurement\Items\ItemService;
use App\Services\Procurement\Orders\OrderService;
use App\Services\Procurement\RFQ\RFQService;
use App\Services\ThirdParties\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
//        $this->authorizeResource(Order::class);
    }

    /**
     * Fetch approved procurement plans with pending direct procurement items.
     */
    public function getDirectPlans(): JsonResponse
    {
        try {
            $planTable = 't_ConsolidatedProcurementPlan';
            $planIdCol = Schema::hasColumn($planTable, 'PlanID') ? 'PlanID' : (Schema::hasColumn($planTable, 'Id') ? 'Id' : 'PlanID');
            $plans = ConsolidatedProcurementPlan::query()
                ->where('Status', ProcurementPlanStatusEnum::Approved)
                ->with(['lineItems.procurementMode'])
                ->whereHas('lineItems', function ($query) {
                    // Require procurement method to be Direct; do not restrict by ExecutionStatus
                    $query->whereHas('procurementMode', function ($sub) {
                        $sub->whereRaw("LOWER(ISNULL(Description,'')) like '%direct%'");
                    });
                })
                // Exclude plans that already have a fully approved Direct PO referencing them
                ->whereRaw("NOT EXISTS (SELECT 1 FROM t_Orders o WHERE RTRIM(LTRIM(ISNULL(o.SourceType,'')))='DIRECT' AND o.DocStatus='a' AND o.SourceId = t_ConsolidatedProcurementPlan.$planIdCol)")
                // Some environments don't have an ApprovedOn column on t_ConsolidatedProcurementPlan.
                // Order by best-available timestamp: SubmittedDate, then CreatedOn, then ModifiedOn.
                ->orderByDesc(DB::raw("COALESCE(SubmittedDate, CreatedOn, ModifiedOn)"))
                ->limit(100)
        ->get()
        ->map(function ($p) {
                    return [
                        'PlanID' => $p->PlanID ?? $p->Id ?? null,
                        'Title' => $p->Title ?? $p->Name ?? ('Plan #' . ($p->PlanID ?? $p->Id)),
                        'FiscalYear' => $p->FiscalYear ?? null,
                        'ApprovedOn' => $p->ApprovedOn,
            'PendingItems' => ($p->lineItems
                ? $p->lineItems
                    ->where('ExecutionStatus', 'Pending')
                    ->filter(function($li){
                        // Count only items whose procurement method is Direct
                        $mode = $li->procurementMode; // App\Models\Core\CodeDetail
                        $desc = is_object($mode) ? ($mode->Description ?? '') : '';
                        return stripos((string)$desc, 'direct') !== false;
                    })
                    ->count()
                : 0),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $plans,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch direct procurement plans', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch direct procurement plans.'
            ], 500);
        }
    }

    /**
     * Get pending direct procurement plan line items for a plan
     */
    public function getDirectPlanItems($planId): JsonResponse
    {
        try {
            $pid = (int) $planId;
            // Use SQL joins to avoid Eloquent relationship issues
            $rows = DB::table('t_PlanLineItem as li')
                ->join('t_CodeDetails as cd', 'cd.ID', '=', 'li.ProcurementMethod')
                ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                ->where(function ($q) use ($pid) { $q->where('li.PlanID', $pid)->orWhere('li.PlanId', $pid); })
                ->where('li.ExecutionStatus', 'Pending')
                ->where(function($q){ $q->where('cd.Description', 'LIKE', '%Direct%'); })
                ->select([
                    DB::raw('COALESCE(it.Id, 0) as itemCode'),
                    DB::raw('COALESCE(it.ItemName, li.Description) as itemName'),
                    DB::raw("COALESCE(it.ItemDescription, li.Description, '') as description"),
                    DB::raw('COALESCE(li.MergedQty, li.Quantity, 0) as quantity'),
                    DB::raw('COALESCE(it.ItemPrice, li.EstimatedUnitCost, 0) as unitPrice'),
                ])
                ->orderBy('itemName')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch direct plan items', ['planId' => $planId, 'error' => $e->getMessage()]);
            // Always return JSON to avoid fetch JSON parse errors on the frontend
            return response()->json(['success' => false, 'data' => [], 'message' => 'Failed to fetch plan items'], 200);
        }
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
                'message' => 'Failed to fetch items.'
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()

    {
        $this->authorize('viewAny', Order::class);

//        User::query()->hasPermission(PermissionEnum::Users->value)->dd();

        try {
            $perPage = (int) request()->query('perPage', 20);
            $perPage = $perPage > 0 ? $perPage : 20;
            $details = $this->orderService->fetchOrdersPaginated($perPage);
            Log::info('PurchaseOrderController@index paginator', ['perPage' => $perPage, 'total' => $details->total()]);
            return view('procurement.orders.index', compact('details'));
        } catch (\Exception $e) {
            Log::error('Create page failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch items.');
        }
//        return view("procurement.orders.index");
    }

    /**
     * Show the form for creating a new resource.
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
                Log::warning('Skipping RFQAward join for awarded RFQs', ['error' => $e->getMessage()]);
                $awardedFromRFQAward = collect();
            }

            // Compute fully-approved PO references to exclude at load time
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
                ->map(function($v){ return is_null($v)?'':trim((string)$v); })
                ->filter()
                ->values()
                ->toArray();

            // Only list RFQs awarded via t_RFQAward (source of truth), excluding ones already converted by approved PO
            $awardedRfqs = $awardedFromRFQAward
                ->filter(function($r) use ($approvedConvertedRFQIds, $usedReferenceNumbers){
                    $rfqNo = trim((string)($r->RFQNumber ?? ''));
                    return !in_array($r->Id, $approvedConvertedRFQIds) && !in_array($rfqNo, $usedReferenceNumbers);
                })
                ->unique('Id')
                ->values();

            // Fallback if needed
            // No fallback to non-awarded RFQs; dropdown must show only awarded RFQs

            $convertedRFQIds = DB::table('t_Orders')
                ->whereRaw("RTRIM(LTRIM(ISNULL(SourceType,'')))='RFQ'")
                ->whereNotNull('SourceId')
                ->where('DocStatus', 'a')
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
                    // Exclude tenders that already have a fully approved Tender-based PO
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

                Log::info('Filtered awarded tenders without contracts', ['count' => $awardedTenders->count()]);
            } catch (\Throwable $e) {
                Log::warning('Skipping TenderAwards join for awarded tenders', ['error' => $e->getMessage()]);
                $awardedTenders = collect();
            }

            $convertedTenderIds = DB::table('t_Orders')
                ->whereRaw("RTRIM(LTRIM(ISNULL(SourceType,'')))='TENDER'")
                ->whereNotNull('SourceId')
                ->where('DocStatus', 'a')
                ->pluck('SourceId')
                ->toArray();

            // Active contracts for Contract-based source selection (Approved or Executed)
            $contracts = DB::table('t_TenderAwards as ta')
                ->leftJoin('t_Suppliers as s', 's.Id', '=', 'ta.WinningSupplierID')
                ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
                ->whereIn('ta.ContractStatus', ['Approved', 'Executed']) // Include both Approved and Executed contracts
                ->whereNotNull('ta.ContractRef') // Must have a contract reference
                ->where('ta.ContractRef', '!=', '') // Contract reference cannot be empty
                // Exclude contracts that already have a fully approved Contract-based PO
                ->whereRaw("NOT EXISTS (SELECT 1 FROM t_Orders o WHERE RTRIM(LTRIM(ISNULL(o.SourceType,'')))='CONTRACT' AND o.DocStatus='a' AND o.SourceId = ta.Id)")
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

            Log::info('Active contracts loaded for LPO', ['count' => $contracts->count()]);

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
                'paymentTerms' => $paymentTerms ?? [], // Pass payment terms to view
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
                'usedReferenceNumbers' => [],
            ])->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseOrderRequest $request)
    {
        $this->authorize('create', Order::class);
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

            // Ensure actor is available for logging/ownership checks
            $actor = $request->user();
            if (!$actor) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthorized'], 401);
                }
                return redirect()->back()->with('error', 'Unauthorized access.');
            }

            // Also block by SourceType+SourceId: don't allow creating a PO for an RFQ/TENDER that already has one
            $reqSourceType = strtoupper((string) $request->input('SourceType', ''));
            $reqSourceId = (int) $request->input('SourceId', 0);
            if (in_array($reqSourceType, ['RFQ', 'TENDER']) && $reqSourceId > 0) {
                try {
                    $existsBySource = DB::table('t_Orders')
                        ->whereRaw('RTRIM(LTRIM(ISNULL(SourceType, \'\')))=?', [trim($reqSourceType)])
                        ->where('SourceId', $reqSourceId)
                        ->exists();
                    if ($existsBySource) {
                        Log::warning('Attempt to create PO for RFQ/Tender that already has a PO', ['sourceType' => $reqSourceType, 'sourceId' => $reqSourceId, 'user' => $actor->Id ?? null]);
                        return response()->json([
                            'message' => 'A purchase order already exists for the selected quotation/tender.',
                            'error' => 'Duplicate source'
                        ], 409);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Source existence check failed', ['error' => $e->getMessage()]);
                    // proceed if the check fails
                }
            }

            // Defensive check: ensure the reference number isn't already used in ExtOrdNum
            if (!empty($referenceNumber)) {
                try {
                    $exists = DB::table('t_Orders')
                        ->whereRaw("RTRIM(LTRIM(ISNULL(ExtOrdNum, '')))=?", [trim((string)$referenceNumber)])
                        ->exists();
                    if ($exists) {
                        Log::warning('Attempt to create PO for reference already used in ExtOrdNum', ['ref' => $referenceNumber, 'user' => $actor->Id ?? null]);
                        return response()->json([
                            'message' => 'A purchase order already exists for the selected reference number.',
                            'error' => 'Duplicate reference'
                        ], 409);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Reference uniqueness check failed', ['error' => $e->getMessage()]);
                    // Proceed conservatively (do not block) if the check itself fails
                }
            }
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
            $uid = null;
            try { $uid = \Illuminate\Support\Facades\Auth::id(); } catch (\Throwable $t) { $uid = null; }
            Log::warning("Unauthorized access attempt to view Order ID: {$id} by user ID: " . ($uid ?? 'guest'));
            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Order ID {$id} not found. Exception: " . $e->getMessage());
            return redirect()->back()->with('error', 'Order not found.');
        } catch (\Exception $e) {
            Log::error("Failed to fetch order ID {$id}. Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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
        $this->authorize('viewAny', Order::class);
        try {
            $RFQ = $this->rfqService->fetchRFQ();
//            \Log::info('RFQ loaded in create():', $RFQ->toArray());
        } catch (\Exception $e) {
            Log::error('Error fetching RFQS: ' . $e->getMessage());
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

            // Fetch payment term description from t_CodeDetails where CodeID = 'PaymentTerm'
            $paymentTermRow = DB::table('t_CodeDetails')->where('CodeID', 'PaymentTerm')->first();
            $paymentTerms = $paymentTermRow->Description ?? null;

            return view('procurement.orders.approval', compact('orderInfo', 'lineInfo', 'paymentTerms'));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $uid = null;
            try { $uid = \Illuminate\Support\Facades\Auth::id(); } catch (\Throwable $t) { $uid = null; }
            Log::warning("Unauthorized access attempt to view Order ID: {$id} by user ID: " . ($uid ?? 'guest'));
            return redirect()->back()->with('error', 'Unauthorized access.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("Order ID {$id} not found. Exception: " . $e->getMessage());
            return redirect()->back()->with('error', 'Order not found.');
        } catch (\Exception $e) {
            Log::error("Failed to fetch order ID {$id}. Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to fetch order.');
        }

    }

    public function approve(ApproveOrderRequest $orderRequest, $id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('approve', $order);
        return $this->documentApprovalService->approve($orderRequest, $id);
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
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $uid = null;
            try { $uid = \Illuminate\Support\Facades\Auth::id(); } catch (\Throwable $t) { $uid = null; }
            Log::warning("Unauthorized access attempt to view RFQ ID: {$id} by user ID: " . ($uid ?? 'guest'));

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error("RFQ ID {$id} not found. Exception: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'RFQ not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error("Failed to fetch RFQ ID {$id}. Exception: " . $e->getMessage(), [
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
        Log::error('Failed to fetch RFQ items', ['rfqId' => $rfqId, 'error' => $e->getMessage()]);
        return response()->json(['items' => []], 200);
    }
}


    public function getAwardedRFQs(): JsonResponse
    {
        try {
            // Exclude ONLY RFQs for which a PO is already fully approved
            // Fully approved POs are marked on t_Orders.DocStatus = 'a'
            $approvedConvertedIds = DB::table('t_Orders')
                ->where('SourceType', 'RFQ')
                ->whereNotNull('SourceId')
                ->where('DocStatus', 'a')
                ->pluck('SourceId')
                ->toArray();

            $approvedUsedRefs = DB::table('t_Orders')
                ->whereNotNull('ExtOrdNum')
                ->where('DocStatus', 'a')
                ->pluck('ExtOrdNum')
                ->map(function($v){ return is_null($v)?'':trim((string)$v); })
                ->filter()
                ->values()
                ->toArray();

            $q = DB::table('t_RFQAward as a')
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
                );

            if (!empty($approvedConvertedIds)) {
                $q->whereNotIn('r.Id', $approvedConvertedIds);
            }
            if (!empty($approvedUsedRefs)) {
                $q->whereNotIn('r.RFQNumber', $approvedUsedRefs);
            }

            $awarded = $q->get();

            return response()->json(['success' => true, 'data' => $awarded]);
        } catch (\Throwable $e) {
            Log::error('getAwardedRFQs failed', ['error' => $e->getMessage()]);
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
                // Exclude tenders already converted to a fully approved PO
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

            Log::info('AJAX: Filtered awarded tenders without contracts', ['count' => $rows->count()]);
            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            Log::error('getAwardedTenders failed', ['error' => $e->getMessage()]);
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
            Log::error('Failed to fetch Tender items', ['tenderId' => $tenderId, 'error' => $e->getMessage()]);
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

            Log::info('Contract tender items loaded', [
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
            Log::error('Failed to fetch Contract tender items', ['contractId' => $contractId, 'error' => $e->getMessage()]);
            return response()->json(['items' => [], 'availableItems' => []], 200);
        }
    }

    /**
     * Root item categories for building the Direct procurement category tree.
     */
    public function getRootItemCategories(): JsonResponse
    {
        try {
            $catTable = 't_ItemCategories';
            $idCol = collect(['Id', 'ID', 'CategoryID'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'Id';
            $nameCol = collect(['Name', 'CategoryName'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'Name';
            $parentCol = collect(['ParentId', 'ParentID', 'Parent'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'ParentId';

            $rows = DB::table($catTable)
                ->where(function($q) use ($parentCol) {
                    $q->whereNull($parentCol)
                      ->orWhere($parentCol, 0);
                })
                ->orderBy($nameCol)
                ->get([$idCol . ' as Id', $nameCol . ' as Name']);

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch root item categories', ['error' => $e->getMessage()]);
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * Return items by category, including descendant categories.
     */
    public function getItemsByCategoryWithDescendants($categoryId): JsonResponse
    {
        try {
            $catTable = 't_ItemCategories';
            $catIdCol = collect(['Id', 'ID', 'CategoryID'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'Id';
            $parentCol = collect(['ParentId', 'ParentID', 'Parent'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'ParentId';

            $allCats = DB::table($catTable)->get([$catIdCol . ' as Id', $parentCol . ' as ParentId']);
            $target = (int) $categoryId;
            $ids = collect([$target]);
            // BFS to gather descendants
            $queue = [$target];
            while ($queue) {
                $current = array_shift($queue);
                $children = $allCats->where('ParentId', $current)->pluck('Id')->all();
                foreach ($children as $cid) {
                    if (!$ids->contains($cid)) {
                        $ids->push($cid);
                        $queue[] = $cid;
                    }
                }
            }

            $items = DB::table('t_Items as i')
                ->leftJoin('t_ItemTypes as it', 'i.ItemType', '=', 'it.Id')
                ->leftJoin('t_ItemCategories as ic', 'i.Category', '=', 'ic.Id')
                ->whereIn('i.Category', $ids->all())
                ->whereNull('i.DeletedBy')
                ->select(
                    DB::raw('COALESCE(i.Id, 0) as itemCode'),
                    DB::raw('COALESCE(i.ItemName, \'\') as itemName'),
                    DB::raw('COALESCE(i.ItemDescription, \'\') as description'),
                    DB::raw('COALESCE(i.ItemPrice, 0) as unitPrice'),
                    DB::raw('COALESCE(it.TypeName, \'\') as itemType'),
                    DB::raw('COALESCE(ic.Name, \'\') as categoryName')
                )
                ->orderBy('i.ItemName')
                ->get();

            return response()->json(['success' => true, 'items' => $items]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch items by category', ['categoryId' => $categoryId, 'error' => $e->getMessage()]);
            return response()->json(['success' => true, 'items' => []]);
        }
    }

    /**
     * Prequalified suppliers for a given item category (considering descendant categories).
     */
    public function prequalifiedSuppliersByCategory($categoryId): JsonResponse
    {
        try {
            // Resolve item category descendants
            $catTable = 't_ItemCategories';
            $catIdCol = collect(['Id', 'ID', 'CategoryID'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'Id';
            $parentCol = collect(['ParentId', 'ParentID', 'Parent'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'ParentId';
            $allCats = DB::table($catTable)->get([$catIdCol . ' as Id', $parentCol . ' as ParentId']);
            $target = (int) $categoryId;
            // Collect descendants of the selected category (including self)
            $descIds = collect([$target]);
            $queue = [$target];
            while ($queue) {
                $current = array_shift($queue);
                $children = $allCats->where('ParentId', $current)->pluck('Id')->all();
                foreach ($children as $cid) {
                    if (!$descIds->contains($cid)) {
                        $descIds->push($cid);
                        $queue[] = $cid;
                    }
                }
            }
            // Collect ancestors of the selected category up to root
            $ancIds = collect([]);
            $walker = $target;
            $guard = 0;
            while ($walker && $guard < 50) {
                $guard++;
                $parent = $allCats->firstWhere('Id', $walker)->ParentId ?? null;
                if ($parent && !$ancIds->contains((int)$parent)) {
                    $ancIds->push((int)$parent);
                    $walker = (int)$parent;
                } else {
                    break;
                }
            }
            // Relevant categories for supplier mapping: self + ancestors + descendants
            $matchCatIds = $descIds->merge($ancIds)->unique()->values();

            // Resolve SC-IC junction columns
            $scicTable = 't_SupplierCategory_ItemCategory';
            $scicSupCol = collect(['SupplierCategoryID', 'SupplierCategoryId', 'supplier_category_id'])->first(fn($c) => Schema::hasColumn($scicTable, $c)) ?? 'SupplierCategoryID';
            $scicItemCol = collect(['ItemCategoryID', 'ItemCategoryId', 'item_category_id'])->first(fn($c) => Schema::hasColumn($scicTable, $c)) ?? 'ItemCategoryID';

            $supplierCategoryIds = DB::table($scicTable)
                ->whereIn($scicItemCol, $matchCatIds->all())
                ->pluck($scicSupCol)
                ->unique()
                ->filter()
                ->values();

            // Map supplier categories to third parties via either pivot or direct supplier table
            $tpsc = 't_ThirdParty_SupplierCategory';
            $hasPivot = Schema::hasTable($tpsc) && (Schema::hasColumn($tpsc, 'ThirdPartyID') || Schema::hasColumn($tpsc, 'third_party_id'));

            $thirdPartyIds = collect();
            if ($hasPivot) {
                $tpscThirdCol = Schema::hasColumn($tpsc, 'ThirdPartyID') ? 'ThirdPartyID' : (Schema::hasColumn($tpsc, 'third_party_id') ? 'third_party_id' : 'ThirdPartyID');
                $tpscSupCol = Schema::hasColumn($tpsc, 'SupplierCategoryID') ? 'SupplierCategoryID' : (Schema::hasColumn($tpsc, 'supplier_category_id') ? 'supplier_category_id' : 'SupplierCategoryID');
                $thirdPartyIds = DB::table($tpsc)
                    ->whereIn($tpscSupCol, $supplierCategoryIds)
                    ->pluck($tpscThirdCol)
                    ->unique()
                    ->filter()
                    ->values();
            }

            // Build supplier list, resilient to different schemas
            $rows = collect();
            if (Schema::hasTable('t_Suppliers')) {
                $supplierQuery = DB::table('t_Suppliers as s')
                    ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID');

                // Prefer filtering by third parties from pivot when available
                if ($thirdPartyIds->isNotEmpty()) {
                    $supplierQuery->whereIn('s.ThirdPartyID', $thirdPartyIds->all());
                } else {
                    // Otherwise, filter by supplier categories mapped to the selected category
                    $supCatCol = Schema::hasColumn('t_Suppliers', 'SupplierCategoryID') ? 'SupplierCategoryID' : (Schema::hasColumn('t_Suppliers', 'CategoryId') ? 'CategoryId' : 'SupplierCategoryID');
                    $supplierQuery->where(function ($q) use ($supCatCol, $supplierCategoryIds, $matchCatIds) {
                        if ($supplierCategoryIds->isNotEmpty()) {
                            $q->whereIn("s.$supCatCol", $supplierCategoryIds->all());
                        }
                        if (Schema::hasColumn('t_Suppliers', 'CategoryId')) {
                            $q->orWhereIn('s.CategoryId', $matchCatIds->all());
                        }
                    });
                }

                // Active suppliers only when column exists
                if (Schema::hasColumn('t_Suppliers', 'Active_Status')) {
                    $supplierQuery->where('s.Active_Status', 1);
                }

                $rows = $supplierQuery->select([
                        DB::raw('COALESCE(tp.Id, s.ThirdPartyID) as ThirdPartyId'),
                        DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                        DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                        DB::raw('COALESCE(s.Id, 0) as SupplierId'),
                    ])
                    ->orderBy('SupplierName')
                    ->get();
            }

            // Fallback: if still empty and we have third party IDs, return third parties directly
            if ($rows->isEmpty() && $thirdPartyIds->isNotEmpty() && Schema::hasTable('t_ThirdParties')) {
                $rows = DB::table('t_ThirdParties as tp')
                    ->whereIn('tp.Id', $thirdPartyIds->all())
                    ->select([
                        DB::raw('tp.Id as ThirdPartyId'),
                        DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                        DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                        DB::raw('CAST(0 as int) as SupplierId'),
                    ])
                    ->orderBy('SupplierName')
                    ->get();
            }

            // Ensure unique by either SupplierId or ThirdPartyId
            $suppliers = $rows->unique(function ($r) {
                return ($r->SupplierId && (int)$r->SupplierId > 0) ? 'S:' . (int)$r->SupplierId : 'TP:' . (int)$r->ThirdPartyId;
            })->values();

            return response()->json(['success' => true, 'data' => $suppliers]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch prequalified suppliers by category', ['categoryId' => $categoryId, 'error' => $e->getMessage()]);
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * Categories present in pending Direct plan items.
     */
    public function getDirectPlanCategories(): JsonResponse
    {
        try {
            $rows = DB::table('t_PlanLineItem as li')
                ->join('t_CodeDetails as cd', 'cd.ID', '=', 'li.ProcurementMethod')
                ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                ->leftJoin('t_ItemCategories as ic', 'ic.Id', '=', 'it.Category')
                ->where('li.ExecutionStatus', 'Pending')
                ->where(function($q){
                    // Be permissive on Description matching to handle different capitalizations/localizations
                    $q->where('cd.Description', 'LIKE', '%Direct%');
                })
                ->groupBy('ic.Id', 'ic.Name')
                ->orderBy('ic.Name')
                ->select([
                    DB::raw('ic.Id as Id'),
                    DB::raw("COALESCE(ic.Name, 'Uncategorized') as Name")
                ])
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch direct plan categories', ['error' => $e->getMessage()]);
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * Direct plan items by item category (optional). Aggregated across all plans with pending Direct items.
     */
    public function getDirectPlanItemsByCategory($categoryId = null): JsonResponse
    {
        try {
            $query = DB::table('t_PlanLineItem as li')
                ->join('t_CodeDetails as cd', 'cd.ID', '=', 'li.ProcurementMethod')
                ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                ->leftJoin('t_ItemCategories as ic', 'ic.Id', '=', 'it.Category')
                ->where('li.ExecutionStatus', 'Pending')
                ->where(function($q){
                    $q->where('cd.Description', 'LIKE', '%Direct%');
                });

            if (!empty($categoryId)) {
                $query->where('ic.Id', (int) $categoryId);
            }

            $items = $query->select([
                    DB::raw('COALESCE(it.Id, 0) as itemCode'),
                    DB::raw('COALESCE(it.ItemName, li.Description) as itemName'),
                    DB::raw('COALESCE(it.ItemDescription, li.Description, \'\') as description'),
                    DB::raw('COALESCE(it.ItemPrice, li.EstimatedUnitCost, 0) as unitPrice'),
                ])
                ->orderBy('itemName')
                ->get();

            return response()->json(['success' => true, 'data' => $items]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch direct plan items by category', ['categoryId' => $categoryId, 'error' => $e->getMessage()]);
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * Get categories available within a specific plan's pending Direct procurement items.
     */
    public function getPlanItemCategories($planId): JsonResponse
    {
        try {
            $pid = (int) $planId;
            $liTable = 't_PlanLineItem';
            $liCatCols = collect(['ItemCategory', 'ItemCategoryID', 'Category', 'CategoryId', 'CategoryID'])
                ->filter(fn($c) => Schema::hasColumn($liTable, $c))
                ->values();
            $liCatNameCols = collect(['ItemCategoryName', 'CategoryName', 'CategoryText'])
                ->filter(fn($c) => Schema::hasColumn($liTable, $c))
                ->values();

            $base = DB::table('t_PlanLineItem as li')
                ->join('t_CodeDetails as cd', 'cd.ID', '=', 'li.ProcurementMethod')
                ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                ->leftJoin('t_ItemCategories as ic', 'ic.Id', '=', 'it.Category')
                ->where(function ($q) use ($pid) {
                    $q->where('li.PlanID', $pid)->orWhere('li.PlanId', $pid);
                })
                ->whereRaw("UPPER(RTRIM(LTRIM(ISNULL(li.ExecutionStatus,''))))='PENDING'")
                ->whereRaw("LOWER(ISNULL(cd.Description,'')) like '%direct%'");

            // Prefer grouping by item categories when available, fallback to line item category refs
            if ($liCatCols->isEmpty() && $liCatNameCols->isEmpty()) {
                $rows = $base->whereNotNull('ic.Id')
                    ->groupBy('ic.Id', 'ic.Name')
                    ->orderBy('ic.Name')
                    ->select([
                        DB::raw('ic.Id as Id'),
                        DB::raw("COALESCE(ic.Name, 'Uncategorized') as Name")
                    ])->get();
            } else {
                // Build union: item categories + line item categories that might not resolve to ic
                $queryA = DB::table('t_PlanLineItem as li')
                    ->join('t_CodeDetails as cd', 'cd.ID', '=', 'li.ProcurementMethod')
                    ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                    ->leftJoin('t_ItemCategories as ic', 'ic.Id', '=', 'it.Category')
                    ->where(function ($q) use ($pid) { $q->where('li.PlanID', $pid)->orWhere('li.PlanId', $pid); })
                    ->whereRaw("UPPER(RTRIM(LTRIM(ISNULL(li.ExecutionStatus,''))))='PENDING'")
                    ->whereRaw("LOWER(ISNULL(cd.Description,'')) like '%direct%'")
                    ->whereNotNull('ic.Id')
                    ->select([
                        DB::raw('ic.Id as Id'),
                        DB::raw("COALESCE(ic.Name, 'Uncategorized') as Name")
                    ]);

                $unions = [$queryA];

                // Numeric line-item category refs -> cast to int Ids (only when casting makes sense)
                if ($liCatCols->isNotEmpty()) {
                    foreach ($liCatCols as $liCatCol) {
                        $qNum = DB::table('t_PlanLineItem as li')
                            ->join('t_CodeDetails as cd', 'cd.ID', '=', 'li.ProcurementMethod')
                            ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                            ->leftJoin('t_ItemCategories as ic', 'ic.Id', '=', 'it.Category')
                            ->where(function ($q) use ($pid) { $q->where('li.PlanID', $pid)->orWhere('li.PlanId', $pid); })
                            ->whereRaw("UPPER(RTRIM(LTRIM(ISNULL(li.ExecutionStatus,''))))='PENDING'")
                            ->whereRaw("LOWER(ISNULL(cd.Description,'')) like '%direct%'")
                            ->whereNull('ic.Id')
                            ->whereNotNull("li.$liCatCol")
                            ->select([
                                DB::raw("TRY_CAST(li.$liCatCol as int) as Id"),
                                DB::raw("'Uncategorized' as Name")
                            ]);
                        $unions[] = $qNum;
                    }
                }

                // Name-based line-item category refs -> map to real category IDs via ic.Name
                $nameCols = $liCatNameCols->isNotEmpty() ? $liCatNameCols : $liCatCols; // treat liCatCols as names too
                foreach ($nameCols as $nmCol) {
                    $qName = DB::table('t_PlanLineItem as li')
                        ->join('t_CodeDetails as cd', 'cd.ID', '=', 'li.ProcurementMethod')
                        ->leftJoin('t_ItemCategories as ic', function($join) use ($nmCol) {
                            $join->on(DB::raw("LOWER(RTRIM(LTRIM(ic.Name)))"), '=', DB::raw("LOWER(RTRIM(LTRIM(li.$nmCol)))"));
                        })
                        ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                        ->where(function ($q) use ($pid) { $q->where('li.PlanID', $pid)->orWhere('li.PlanId', $pid); })
                        ->whereRaw("UPPER(RTRIM(LTRIM(ISNULL(li.ExecutionStatus,''))))='PENDING'")
                        ->whereRaw("LOWER(ISNULL(cd.Description,'')) like '%direct%'")
                        ->whereNotNull("li.$nmCol")
                        ->whereNotNull('ic.Id')
                        ->select([
                            DB::raw('ic.Id as Id'),
                            DB::raw("COALESCE(ic.Name, 'Uncategorized') as Name")
                        ]);
                    $unions[] = $qName;
                }

                // Union all and finalize
                $rows = array_shift($unions);
                foreach ($unions as $u) { $rows = $rows->union($u); }
                $rows = $rows->get()
                    ->unique('Id')
                    ->filter(fn($r) => !empty($r->Id))
                    ->values()
                    ->sortBy('Name')
                    ->values();
            }

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch plan item categories', ['planId' => $planId, 'error' => $e->getMessage()]);
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * Get a plan's pending Direct procurement items filtered by category.
     */
    public function getPlanItemsByCategory($planId, $categoryId): JsonResponse
    {
        try {
            $pid = (int) $planId;
            $cid = (int) $categoryId;
            $driver = DB::connection()->getDriverName();
            $castInt = function (string $expr) use ($driver) {
                switch ($driver) {
                    case 'sqlsrv':
                        return "TRY_CAST($expr as int)";
                    case 'mysql':
                    case 'mariadb':
                        // Avoid casting empty strings to 0; NULLIF handles ''
                        return "CAST(NULLIF($expr,'') AS UNSIGNED)";
                    case 'pgsql':
                        // Strip non-digits before casting; returns NULL if empty
                        return "NULLIF(REGEXP_REPLACE($expr, '[^0-9]', '', 'g'), '')::int";
                    default:
                        return "CAST($expr as int)";
                }
            };
            // Helper to safely apply an IN(...) for computed string expressions by expanding into OR-equals with bindings
            $applyStringIn = function ($q, string $expr, array $values) {
                $vals = array_values(array_filter($values, fn($v) => $v !== null && $v !== ''));
                if (empty($vals)) { return; }
                $q->orWhere(function ($qq) use ($expr, $vals) {
                    foreach ($vals as $v) {
                        $qq->orWhereRaw("$expr = ?", [$v]);
                    }
                });
            };
            // Detect dynamic columns for descriptions/prices/quantities and code details description
            $liTable = 't_PlanLineItem';
            $cdTable = 't_CodeDetails';
            // Only include descriptive text columns; exclude li.ItemName to avoid numeric codes appearing as itemName
            $liDescCandidates = [
                'Description',
                'ItemDescription',
                'ManualItemDescription',
                'DescriptionOfRequirement',
                'DescriptionText',
                'Details',
                'Narration',
                'ReqDescription',
                'Remarks'
            ];
            $liQtyCandidates = ['MergedQty', 'Quantity', 'Qty', 'PlannedQty', 'QtyToProcure'];
            $liUnitCostCandidates = ['EstimatedUnitCost', 'EstUnitCost', 'EstimateUnitCost', 'UnitPrice', 'EstimatedCost', 'UnitCost'];
            $cdDescCandidates = ['Description', 'Descriptions', 'Desc', 'Name', 'CodeDescription', 'DescriptionText'];

            $liDescColsAvail = array_values(array_filter($liDescCandidates, fn($c) => Schema::hasColumn($liTable, $c)));
            $liQtyColsAvail = array_values(array_filter($liQtyCandidates, fn($c) => Schema::hasColumn($liTable, $c)));
            $liUnitCostColsAvail = array_values(array_filter($liUnitCostCandidates, fn($c) => Schema::hasColumn($liTable, $c)));
            $cdDescCol = collect($cdDescCandidates)->first(fn($c) => Schema::hasColumn($cdTable, $c)) ?? 'Description';

            // Build COALESCE expressions
            $liDescExpr = !empty($liDescColsAvail)
                ? ('COALESCE(' . implode(', ', array_map(fn($c) => "li.$c", $liDescColsAvail)) . ')')
                : "''";
            $liQtyExpr = 'COALESCE(' . (empty($liQtyColsAvail) ? '0' : implode(', ', array_map(fn($c) => "li.$c", $liQtyColsAvail))) . ', 0)';
            $liUnitCostExpr = 'COALESCE(' . (empty($liUnitCostColsAvail) ? '0' : implode(', ', array_map(fn($c) => "li.$c", $liUnitCostColsAvail))) . ', 0)';
            // Resolve descendant category IDs (include selected)
            $catTable = 't_ItemCategories';
            $catIdCol = collect(['Id', 'ID', 'CategoryID'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'Id';
            $catNameCol = collect(['Name', 'CategoryName', 'Description'])
                ->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'Name';
            $parentCol = collect(['ParentId', 'ParentID', 'Parent'])->first(fn($c) => Schema::hasColumn($catTable, $c)) ?? 'ParentId';
            $allCats = DB::table($catTable)->get([$catIdCol . ' as Id', $parentCol . ' as ParentId', $catNameCol . ' as Name']);
            $selCatName = optional($allCats->firstWhere('Id', $cid))->Name;
            $descIds = collect([$cid]);
            $queue = [$cid];
            while ($queue) {
                $current = array_shift($queue);
                $children = $allCats->where('ParentId', $current)->pluck('Id')->all();
                foreach ($children as $childId) {
                    if (!$descIds->contains($childId)) {
                        $descIds->push($childId);
                        $queue[] = $childId;
                    }
                }
            }

            // Also collect descendant category names (normalized) for string-based matching fallbacks
            $descNamesLower = $allCats->whereIn('Id', $descIds->all())
                ->pluck('Name')
                ->filter()
                ->map(function ($n) { return strtolower(trim((string)$n)); })
                ->unique()
                ->values()
                ->all();

            // Detect possible line item category columns
            $liTable = 't_PlanLineItem';
            $liCatCols = collect(['ItemCategory', 'ItemCategoryID', 'Category', 'CategoryId', 'CategoryID'])
                ->filter(fn($c) => Schema::hasColumn($liTable, $c))
                ->values();
            $liCatNameCols = collect(['ItemCategoryName', 'CategoryName', 'CategoryText'])
                ->filter(fn($c) => Schema::hasColumn($liTable, $c))
                ->values();

            // Detect item->category reference column and category PK column for robust joins
            $itemsTable = 't_Items';
            $itemCatRef = collect(['Category', 'CategoryID', 'CategoryId', 'ItemCategory', 'ItemCategoryID'])
                ->first(fn($c) => Schema::hasColumn($itemsTable, $c)) ?? 'Category';
            $itemCatNameCols = collect(['CategoryName', 'ItemCategoryName', 'ItemCategory'])
                ->filter(fn($c) => Schema::hasColumn($itemsTable, $c))
                ->values();
            // Reuse $catTable and $catIdCol from above

            $itemsQ = DB::table('t_PlanLineItem as li')
                ->join('t_CodeDetails as cd', 'cd.ID', '=', 'li.ProcurementMethod')
                ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                ->leftJoin('t_ItemCategories as ic', function($join) use ($catIdCol, $itemCatRef) {
                    $join->on(DB::raw("ic.$catIdCol"), '=', DB::raw("it.$itemCatRef"));
                })
                ->where(function ($q) use ($pid) {
                    $q->where('li.PlanID', $pid)->orWhere('li.PlanId', $pid);
                })
                // Case-insensitive Pending
                ->whereRaw("UPPER(RTRIM(LTRIM(ISNULL(li.ExecutionStatus,''))))='PENDING'")
                // Case-insensitive Direct
                ->whereRaw("LOWER(ISNULL(cd.$cdDescCol,'')) like '%direct%'");

                        // Apply category filter by:
                        // - numeric category IDs on item or line-item (TRY_CAST)
                        // - string-based category names on item/line-item/category (normalized lower trim) as fallback
                                    $itemsQ->where(function ($q) use ($descIds, $liCatCols, $liCatNameCols, $catIdCol, $catNameCol, $itemCatRef, $itemCatNameCols, $descNamesLower, $castInt, $applyStringIn) {
                                            $ids = $descIds->all();
                                            $idStr = array_map('strval', $ids);
                            // Numeric matches
                                            $q->whereIn(DB::raw($castInt("it.$itemCatRef")), $ids)
                                                ->orWhereIn(DB::raw($castInt("ic.$catIdCol")), $ids)
                                                // Direct string equality fallback
                                                ->orWhereIn("it.$itemCatRef", $idStr)
                                                ->orWhereIn("ic.$catIdCol", $idStr);
                            foreach ($liCatCols as $col) {
                                                    $q->orWhereIn(DB::raw($castInt("li.$col")), $ids)
                                                        ->orWhereIn("li.$col", $idStr);
                            }
                            // Name-based fallbacks
                            if (!empty($descNamesLower)) {
                                $applyStringIn($q, "LOWER(RTRIM(LTRIM(ic.$catNameCol)))", $descNamesLower);
                                foreach ($liCatNameCols as $col) {
                                    $applyStringIn($q, "LOWER(RTRIM(LTRIM(li.$col)))", $descNamesLower);
                                }
                                foreach ($itemCatNameCols as $col) {
                                    $applyStringIn($q, "LOWER(RTRIM(LTRIM(it.$col)))", $descNamesLower);
                                }
                            }
                        });

            $items = $itemsQ->select([
                    DB::raw('COALESCE(it.Id, 0) as itemCode'),
                    DB::raw("COALESCE(NULLIF(RTRIM(LTRIM(it.ItemName)), ''), $liDescExpr) as itemName"),
                    DB::raw("COALESCE(it.ItemDescription, " . $liDescExpr . ", '') as description"),
                    DB::raw($liQtyExpr . ' as quantity'),
                    DB::raw('COALESCE(it.ItemPrice, ' . $liUnitCostExpr . ', 0) as unitPrice'),
                ])
                ->orderBy('itemName')
                ->get();

            if ($items->isEmpty()) {
                // Fallback: Populate from plan needs without strict Direct/Pending filters
                $fallbackQ = DB::table('t_PlanLineItem as li')
                    ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                    ->leftJoin('t_ItemCategories as ic', function($join) use ($catIdCol, $itemCatRef) {
                        $join->on(DB::raw("ic.$catIdCol"), '=', DB::raw("it.$itemCatRef"));
                    })
                    ->where(function ($q) use ($pid) {
                        $q->where('li.PlanID', $pid)->orWhere('li.PlanId', $pid);
                    });

                                $fallbackQ->where(function ($q) use ($descIds, $liCatCols, $liCatNameCols, $catIdCol, $catNameCol, $itemCatRef, $itemCatNameCols, $descNamesLower, $castInt, $applyStringIn) {
                                        $ids = $descIds->all();
                                        $idStr = array_map('strval', $ids);
                    // Numeric
                                        $q->whereIn(DB::raw($castInt("it.$itemCatRef")), $ids)
                                            ->orWhereIn(DB::raw($castInt("ic.$catIdCol")), $ids)
                                            ->orWhereIn("it.$itemCatRef", $idStr)
                                            ->orWhereIn("ic.$catIdCol", $idStr);
                    foreach ($liCatCols as $col) {
                                                $q->orWhereIn(DB::raw($castInt("li.$col")), $ids)
                                                    ->orWhereIn("li.$col", $idStr);
                    }
                    // Names
                    if (!empty($descNamesLower)) {
                        $applyStringIn($q, "LOWER(RTRIM(LTRIM(ic.$catNameCol)))", $descNamesLower);
                        foreach ($liCatNameCols as $col) {
                            $applyStringIn($q, "LOWER(RTRIM(LTRIM(li.$col)))", $descNamesLower);
                        }
                        foreach ($itemCatNameCols as $col) {
                            $applyStringIn($q, "LOWER(RTRIM(LTRIM(it.$col)))", $descNamesLower);
                        }
                    }
                });

                $items = $fallbackQ->select([
                        DB::raw('COALESCE(it.Id, 0) as itemCode'),
                        DB::raw("COALESCE(NULLIF(RTRIM(LTRIM(it.ItemName)), ''), $liDescExpr) as itemName"),
                        DB::raw("COALESCE(it.ItemDescription, " . $liDescExpr . ", '') as description"),
                        DB::raw($liQtyExpr . ' as quantity'),
                        DB::raw('COALESCE(it.ItemPrice, ' . $liUnitCostExpr . ', 0) as unitPrice'),
                    ])
                    ->orderBy('itemName')
                    ->get();

                Log::info('Fallback used for plan items by category', [
                    'planId' => $pid,
                    'categoryId' => $cid,
                    'resultCount' => $items->count(),
                ]);
            }

            $debug = [
                'planId' => $pid,
                'categoryId' => $cid,
                'selectedCategoryName' => $selCatName,
                'descendantIds' => $descIds->all(),
                'descendantNames' => $descNamesLower,
                'liCatCols' => $liCatCols->all(),
                'liCatNameCols' => $liCatNameCols->all(),
                'itemCatRef' => $itemCatRef,
                'itemCatNameCols' => $itemCatNameCols->all(),
                'driver' => $driver,
            ];

            if ($items->isEmpty()) {
                Log::info('No plan items found for category filter', [
                    'planId' => $pid,
                    'categoryId' => $cid,
                    'descIds' => $descIds->all(),
                    'liCatCols' => $liCatCols->all(),
            'liCatNameCols' => $liCatNameCols->all(),
            'itemCatRef' => $itemCatRef,
            'itemCatNameCols' => $itemCatNameCols->all(),
            'descNamesLower' => $descNamesLower,
                ]);

                // Final fallback: return all plan needs (lines) regardless of category so user can proceed
                $items = DB::table('t_PlanLineItem as li')
                    ->leftJoin('t_Items as it', 'it.Id', '=', 'li.ItemID')
                    ->where(function ($q) use ($pid) {
                        $q->where('li.PlanID', $pid)->orWhere('li.PlanId', $pid);
                    })
                    ->select([
                        DB::raw('COALESCE(it.Id, 0) as itemCode'),
                        DB::raw('COALESCE(it.ItemName, li.Description) as itemName'),
                        DB::raw('COALESCE(it.ItemDescription, li.Description, "") as description'),
                        DB::raw('COALESCE(li.MergedQty, li.Quantity, 0) as quantity'),
                        DB::raw('COALESCE(it.ItemPrice, li.EstimatedUnitCost, 0) as unitPrice'),
                    ])
                    ->orderBy('itemName')
                    ->get();
                Log::info('All-plan-needs fallback used', ['planId' => $pid, 'count' => $items->count()]);
                $debug['fallbackAllPlanNeedsCount'] = $items->count();
            }

            $payload = ['success' => true, 'data' => $items];
            if ((int) request('debug', 0) === 1) {
                $payload['debug'] = $debug;
            }
            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch plan items by category', ['planId' => $planId, 'categoryId' => $categoryId, 'error' => $e->getMessage()]);
            if ((int) request('debug', 0) === 1) {
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'debug' => [
                        'error' => $e->getMessage(),
                        'trace' => app()->environment('local') ? $e->getTraceAsString() : null,
                    ],
                ], 200);
            }
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * Wrapper: prequalified suppliers for a category (plan-agnostic).
     */
    public function getPrequalifiedSuppliers($categoryId): JsonResponse
    {
        return $this->prequalifiedSuppliersByCategory($categoryId);
    }
}
