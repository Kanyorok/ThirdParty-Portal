<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQResponse;
use App\Models\ThirdParies\Supplier;
use App\Services\Procurement\RFQ\RFQWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFQController extends Controller
{
    /**
     * How long (seconds) to cache a successful store response for idempotency replay.
     */
    private const IDEMPOTENCY_TTL = 86400; // 24 hours

    /**
     * How long (seconds) to hold the processing lock.
     * Must exceed the slowest expected execution of the store operation.
     */
    private const IDEMPOTENCY_LOCK_TTL = 30;

    public function __construct(protected RFQWorkflowService $workflowService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RFQ::class);

        // Build base query with explicit join to get status description
        $query = DB::table('t_RFQ as rfq')
            ->leftJoin('t_CodeDetails as cd', function ($join) {
                $join->on('rfq.Status', '=', 'cd.Value')
                     ->where('cd.CodeId', '=', 'RequisitionStatus')
                     ->where('cd.IsActive', '=', 1)
                     ->whereNull('cd.DeletedOn');
            })
            ->leftJoin('t_Requisitions as req', 'rfq.RequisitionId', '=', 'req.Id')
            ->whereNull('rfq.DeletedOn')
            ->select(
                'rfq.Id',
                'rfq.RFQNumber',
                'rfq.RequisitionId',
                'rfq.Status',
                'rfq.SubmissionDeadline',
                'rfq.CreatedBy',
                'rfq.CreatedOn',
                'rfq.Comments',
                'cd.Description as StatusDescription',
                'req.RequisitionNo'
            );

        // Apply filters
        if ($request->filled('status')) {
            $query->where('rfq.Status', $request->query('status'));
        }
        if ($request->filled('created_by')) {
            $query->where('rfq.CreatedBy', $request->query('created_by'));
        }

        // Sorting
        $allowedSorts = [
            'RFQNumber' => 'rfq.RFQNumber',
            'Status' => 'rfq.Status',
            'SubmissionDeadline' => 'rfq.SubmissionDeadline',
            'CreatedOn' => 'rfq.CreatedOn',
            'CreatedBy' => 'rfq.CreatedBy',
        ];

        $sortBy = $request->query('sort_by');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->orderBy('rfq.Id', 'desc');
        }

        // Paginate
        $rfqs = $query->paginate(10)->withQueryString();

        // Get requisitions for the modal
        $requisitions = $this->getAvailableRequisitions();

        // Build CreatedBy map
        $createdByIds = $rfqs->pluck('CreatedBy')->unique()->filter()->values()->all();
        $createdByMap = [];
        if (! empty($createdByIds)) {
            $users = DB::table('t_Users')->whereIn('Id', $createdByIds)->select('Id', 'Name')->get();
            foreach ($users as $u) {
                $createdByMap[$u->Id] = $u->Name;
            }
        }

        // For filter dropdowns
        $statuses = DB::table('t_RFQ')->select('Status')->distinct()->pluck('Status')->filter()->values();
        $allUsers = DB::table('t_Users')->select('Id', 'Name')->orderBy('Name')->get();

        return view('procurement.rfqs.index', compact('rfqs', 'requisitions', 'createdByMap', 'statuses', 'allUsers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', RFQ::class);

        $requisitions = $this->getAvailableRequisitions();
        $categories = ItemCategories::all();
        $suppliers = Supplier::all();

        return view('procurement.rfqs.create', compact('categories', 'suppliers', 'requisitions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * Idempotency strategy
     * ─────────────────────────────────────────────────────────────────────────
     * 1. Client sends a unique `Idempotency-Key` header per *intended* request
     *    (e.g. a UUID generated in the browser before the form submit).
     *    If no header is present we fall back to a hash of the validated
     *    payload — good enough for simple UIs that don't support headers.
     *
     * 2. First call   → acquire lock → run → cache success result → release
     *                   lock → return result.
     *
     * 3. Duplicate    → cached result found → redirect to the same RFQ
     *    (success)       without touching the DB.
     *
     * 4. In-flight    → lock is held by another process → return error so the
     *                   client knows to wait briefly and try again.
     *
     * 5. Failed call  → result is NOT cached → next request is treated as a
     *                   fresh attempt and allowed to proceed normally.
     *
     * Additionally, RFQ number generation is wrapped in a DB-level lock so
     * concurrent requests can never generate the same number.
     */
    public function store(Request $request)
    {
        $this->authorize('create', RFQ::class);

        $validated = $request->validate([
            'RequisitionId' => 'required|exists:t_Requisitions,Id',
            'Comments' => 'nullable|string|max:255',
            'SubmissionDeadline' => ['required', 'date', 'after:today'],
        ], [
            'SubmissionDeadline.after' => 'Submission deadline must be at least tomorrow.',
        ]);

        $actor = Auth::user();


        //  Build the idempotency key
        //    Prefer an explicit client-supplied header so two genuinely
        //    *different* RFQs with identical fields never collide.

        $clientKey = $request->header('Idempotency-Key');

        $idempotencyKey = $clientKey
            ? 'rfq_idem:' . $actor->Id . ':' . $clientKey
            : 'rfq_idem:' . $actor->Id . ':' . md5(json_encode($validated));

        $resultCacheKey = $idempotencyKey . ':result';
        $lockKey = $idempotencyKey . ':lock';


        //    The client gets redirected to the same RFQ without a DB write.

        $cached = Cache::get($resultCacheKey);

        if ($cached !== null) {
            Log::info('Idempotent replay for RFQ store.', [
                'user_id' => $actor->Id,
                'idempotency_key' => $idempotencyKey,
                'rfq_id' => $cached['rfq_id'],
            ]);

            return redirect()->route('rfqs.show', $cached['rfq_id'])
                ->with('success', 'RFQ already created successfully (duplicate request ignored).');
        }



        //    If another request with the same key is currently being
        //    processed, bail out instead of queuing behind it.

        $lock = Cache::lock($lockKey, self::IDEMPOTENCY_LOCK_TTL);

        if (! $lock->get()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Your previous request is still being processed. Please wait a moment and try again.');
        }

        try {
            // Re-check the cache inside the lock in case another process
            // succeeded between steps 2 and 3 (double-checked locking).
            $cached = Cache::get($resultCacheKey);

            if ($cached !== null) {
                return redirect()->route('rfqs.show', $cached['rfq_id'])
                    ->with('success', 'RFQ already created successfully (duplicate request ignored).');
            }


            // 4.Generate RFQ number and create the record inside a DB
            //    transaction. Using lockForUpdate() on the last-RFQ query
            //    prevents two concurrent requests from generating the same
            //    number even if the cache lock somehow allows both through.

            $rfq = DB::transaction(function () use ($validated, $actor) {
                $prefix = 'RFQ-';

                // Lock the row so concurrent transactions queue here instead
                // of reading the same "last number" simultaneously.
                $lastRFQ = RFQ::where('RFQNumber', 'like', $prefix . '%')
                    ->orderBy('Id', 'desc')
                    ->lockForUpdate()
                    ->first();

                $lastNumber = $lastRFQ ? intval(substr($lastRFQ->RFQNumber, strlen($prefix))) : 0;
                $newRFQNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

                // Get the proper status value for 'Pending'
                $pendingStatus = DB::table('t_CodeDetails')
                    ->where('CodeId', 'RequisitionStatus')
                    ->where('Description', 'Pending')
                    ->where('IsActive', 1)
                    ->whereNull('DeletedOn')
                    ->value('Value');

                return RFQ::create([
                    'RFQNumber' => $newRFQNumber,
                    'RequisitionId' => $validated['RequisitionId'],
                    'Comments' => $validated['Comments'] ?? null,
                    'SubmissionDeadline' => $validated['SubmissionDeadline'],
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'Status' => $pendingStatus ?? 'pe',
                ]);
            });

            Log::info('RFQ created successfully', [
                'rfq_id' => $rfq->Id,
                'rfq_number' => $rfq->RFQNumber,
                'status' => $rfq->Status,
                'user_id' => $actor->Id,
            ]);

            //  Cache the success result so retries are replayed (step 3).
            //    Failures are intentionally NOT cached so they can be retried.

            Cache::put($resultCacheKey, ['rfq_id' => $rfq->Id], self::IDEMPOTENCY_TTL);

            return redirect()->route('rfqs.show', $rfq->Id)
                ->with('success', 'RFQ created successfully.');

        } catch (\Throwable $e) {
            Log::error('Failed to create RFQ.', [
                'user_id' => $actor->Id,
                'input' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Do NOT cache — allow the user to retry.
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create RFQ. Please try again.');

        } finally {
            // Always release the lock, even if an exception was thrown.
            // Without this it would be held for IDEMPOTENCY_LOCK_TTL seconds,
            // blocking every retry for that window.
            $lock->release();
        }
    }

    /**
     * Submit the RFQ for approval (Workflow).
     */
    public function submit(Request $request, $id)
    {
        $rfq = RFQ::findOrFail($id);
        $this->authorize('create', $rfq);

        Log::info('Submit for approval request received', [
            'rfq_id' => $rfq->Id,
            'rfq_number' => $rfq->RFQNumber,
            'current_status' => $rfq->Status,
            'user_id' => Auth::user()->Id,
            'user_name' => Auth::user()->Name,
        ]);

        // Check if RFQ has at least one line item
        if ($rfq->rfqLines()->count() < 1) {
            Log::warning('Submit blocked - no line items', ['rfq_id' => $rfq->Id]);

            return redirect()->back()->with('error', 'Cannot submit an RFQ without any items. Please add at least one RFQ line.');
        }

        // Check if RFQ is already submitted (has pending approvals)
        $pendingApprovals = $this->workflowService->getPendingApprovals($rfq);
        if (count($pendingApprovals) > 0) {
            return redirect()->back()->with('error', 'RFQ is already submitted for approval.');
        }

        try {
            $result = $this->workflowService->submitRFQ($rfq, Auth::user(), 'Submitted via UI');

            // Refresh to get updated status
            $rfq->refresh();

            Log::info('Submit for approval completed', [
                'rfq_id' => $rfq->Id,
                'result' => $result,
                'new_status' => $rfq->Status,
            ]);

            if ($result) {
                return redirect()->back()->with('success', 'RFQ submitted for Approval successfully.');
            }

            return redirect()->back()->with('error', 'Failed to submit RFQ for approval. Please check workflow configuration.');

        } catch (\App\Exceptions\ErroredException $e) {
            Log::warning('Submit blocked by workflow service', [
                'rfq_id' => $rfq->Id,
                'reason' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            Log::error('Failed to submit RFQ', [
                'rfq_id' => $rfq->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to submit RFQ for approval. Please try again.');
        }
    }

    /**
     * Approve the RFQ (Workflow).
     */
    public function approve(Request $request, $id)
    {
        $rfq = RFQ::findOrFail($id);

        Log::info('Approval request received', [
            'rfq_id' => $rfq->Id,
            'rfq_number' => $rfq->RFQNumber,
            'current_status' => $rfq->Status,
            'user_id' => Auth::user()->Id,
            'user_name' => Auth::user()->Name,
        ]);

        // Check if RFQ has at least one line item
        if ($rfq->rfqLines()->count() < 1) {
            Log::warning('Approval blocked - no line items', ['rfq_id' => $rfq->Id]);

            return redirect()->back()->with('error', 'Cannot approve an RFQ without any items.');
        }

        // Use Workflow Service to approve
        $result = $this->workflowService->approveRFQ($rfq, Auth::user(), 'Approved via UI');

        // Refresh to get updated status
        $rfq->refresh();

        Log::info('Approval process completed', [
            'rfq_id' => $rfq->Id,
            'result' => $result,
            'new_status' => $rfq->Status,
            'status_description' => $rfq->status_description ?? 'N/A',
        ]);

        if ($result) {
            return redirect()->back()->with('success', 'RFQ has been approved successfully.');
        }

        return redirect()->back()->with('error', 'Failed to approve RFQ. Please check workflow configuration.');
    }

    /**
     * Publish the RFQ to suppliers (Send Emails).
     */
    public function publish(Request $request, $id)
    {
        $rfq = RFQ::findOrFail($id);

        Log::info('Publish request received', [
            'rfq_id' => $rfq->Id,
            'rfq_number' => $rfq->RFQNumber,
            'current_status' => $rfq->Status,
        ]);

        // Get the approved status value from CodeDetails
        $approvedStatusValue = DB::table('t_CodeDetails')
            ->where('CodeId', 'RequisitionStatus')
            ->where('Description', 'Approved')
            ->where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->value('Value');

        Log::info('Checking approval status', [
            'current_status' => $rfq->Status,
            'expected_approved_value' => $approvedStatusValue,
        ]);

        // Check if RFQ is approved (case-insensitive comparison)
        if (! $approvedStatusValue || strtolower($rfq->Status) !== strtolower($approvedStatusValue)) {
            Log::warning('Publish blocked - RFQ not approved', [
                'rfq_id' => $rfq->Id,
                'current_status' => $rfq->Status,
                'expected_status' => $approvedStatusValue,
            ]);

            return redirect()->back()->with('error', 'RFQ must be approved before publishing to suppliers.');
        }

        $request->validate([
            'suppliers' => 'required|array|min:1',
            'suppliers.*' => 'exists:t_SupplierMaster,Id',
        ], [
            'suppliers.required' => 'Please select at least one supplier.',
            'suppliers.min' => 'Please select at least one supplier.',
        ]);

        $supplierMasterIds = collect($request->suppliers)
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        Log::info('Publishing to suppliers (Master IDs)', [
            'rfq_id' => $rfq->Id,
            'supplier_master_ids' => $supplierMasterIds,
        ]);

        $supplierIds = [];
        foreach ($supplierMasterIds as $masterId) {
            // Check if supplier exists in t_Suppliers
            $supplier = DB::table('t_Suppliers')
                ->where('SupplierMasterId', $masterId)
                ->whereNull('DeletedOn')
                ->first();

            if (! $supplier) {
                // Get a valid category ID
                $categoryId = $rfq->ItemCategoryId ?? DB::table('t_ItemCategories')->value('Id');

                // Get a valid Supplier Category ID
                $supplierCategoryId = DB::table('t_SupplierCategory_ItemCategory')
                    ->where('ItemCategoryID', $categoryId)
                    ->value('SupplierCategoryID')
                    ?? DB::table('t_SupplierCategories')->value('SupplierCategoryID');

                $newSupplierId = DB::table('t_Suppliers')->insertGetId([
                    'SupplierMasterId' => $masterId,
                    'CategoryId' => $supplierCategoryId,
                    'SupplierCategoryID' => $supplierCategoryId,
                    'Active_Status' => 1,
                    'CreatedBy' => Auth::user()->Id,
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::user()->Id,
                    'ModifiedOn' => now(),
                ]);

                $supplierIds[] = $newSupplierId;
                Log::info('Created new t_Suppliers record', ['master_id' => $masterId, 'new_id' => $newSupplierId]);
            } else {
                $supplierIds[] = $supplier->Id;
            }
        }

        // Update supplier statuses in the pivot table
        $rfq->suppliers()->syncWithPivotValues($supplierIds, ['Status' => 'Approved']);

        // Get the Published status value
        $publishedStatusValue = DB::table('t_CodeDetails')
            ->where('CodeId', 'RequisitionStatus')
            ->where('Description', 'Published')
            ->where('IsActive', 1)
            ->whereNull('DeletedOn')
            ->value('Value');

        // Update RFQ status to Published
        $rfq->update([
            'Status' => $publishedStatusValue ?? 'Pub',
            'ModifiedBy' => Auth::user()->Id,
        ]);

        Log::info('RFQ status updated to Published', [
            'rfq_id' => $rfq->Id,
            'new_status' => $rfq->Status,
        ]);

        // Build recipients (unique emails for selected suppliers)
        $thirdPartyUserEmailSub = DB::table('t_ThirdPartyUsers as tpu')
            ->select('tpu.ThirdPartyId', DB::raw('MIN(tpu.Email) as Email'))
            ->whereNull('tpu.DeletedOn')
            ->groupBy('tpu.ThirdPartyId');

        $recipientRows = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
            ->join('t_ThirdParties as tp', 'sm.ThirdPartyId', '=', 'tp.Id')
            ->leftJoinSub($thirdPartyUserEmailSub, 'tpu', function ($join) {
                $join->on('tpu.ThirdPartyId', '=', 'tp.Id');
            })
            ->whereIn('s.Id', $supplierIds)
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->whereNull('tp.DeletedOn')
            ->select('tp.TradingName', DB::raw('tpu.Email as Email'))
            ->get();

        $recipients = [];
        $usedEmails = [];

        foreach ($recipientRows as $row) {
            $email = trim((string) $row->Email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && ! in_array(strtolower($email), $usedEmails, true)) {
                $recipients[] = ['name' => $row->TradingName, 'email' => $email];
                $usedEmails[] = strtolower($email);
            }
        }

        // Send emails to suppliers
        $actor = Auth::user();
        $successfulEmails = 0;

        if ($actor && ! empty($recipients)) {
            $subject = 'RFQ Invitation: ' . $rfq->RFQNumber;
            $submissionDeadlineFormatted = $rfq->SubmissionDeadline
                ? \Carbon\Carbon::parse($rfq->SubmissionDeadline)->format('d/m/Y')
                : 'N/A';

            $bodyTemplate = '<p>You are invited to submit a quotation for RFQ <b>' . e($rfq->RFQNumber) . '</b>.</p>' .
                '<p>Submission Deadline: <b>' . e($submissionDeadlineFormatted) . '</b></p>' .
                '<p>Please log in to the supplier portal to view details and submit your response.</p>';

            foreach ($recipients as $recipient) {
                $to = [[$recipient['name'] => $recipient['email']]];
                $personalBody = '<p>Hello ' . e($recipient['name']) . ',</p>' . $bodyTemplate;

                try {
                    $service = \App\Services\CRMEmailService::createRaw(
                        $actor,
                        $subject,
                        $personalBody,
                        $to,
                        'ThirdParty',
                        '',
                        [],
                        [],
                        \App\Enums\EmailPriorityEnum::Important
                    );
                    $service->send(true);
                    $successfulEmails++;

                    Log::info('Email sent to supplier', [
                        'rfq_id' => $rfq->Id,
                        'recipient' => $recipient['email'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send email to supplier', [
                        'rfq_id' => $rfq->Id,
                        'recipient' => $recipient['email'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'RFQ published. ' . $successfulEmails . ' invitation email(s) sent.');
    }

    /**
     * Reject the RFQ.
     */
    public function reject(Request $request, $id)
    {
        $rfq = RFQ::findOrFail($id);
        $this->authorize('reject', $rfq);

        $request->validate([
            'RejectionReason' => 'required|string|max:255',
        ]);

        Log::info('Rejection request received', [
            'rfq_id' => $rfq->Id,
            'user_id' => Auth::user()->Id,
            'reason' => $request->RejectionReason,
        ]);

        // Check if RFQ has at least one line item
        if ($rfq->rfqLines()->count() < 1) {
            return redirect()->back()->with('error', 'Cannot reject an RFQ without any items.');
        }

        $result = $this->workflowService->rejectRFQ($rfq, Auth::user(), $request->RejectionReason);

        $rfq->refresh();

        Log::info('Rejection completed', [
            'rfq_id' => $rfq->Id,
            'new_status' => $rfq->Status,
            'result' => $result,
        ]);

        if ($result) {
            return redirect()->back()->with('success', 'RFQ has been rejected successfully.');
        }

        return redirect()->back()->with('error', 'Failed to reject RFQ.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rfq = RFQ::with('rfqLines', 'rfqLines.uom', 'requisition')->findOrFail($id);
        $this->authorize('view', $rfq);

        // Gather item category IDs from RFQ lines and include ancestors and descendants
        $itemCategoryIds = $rfq->rfqLines->pluck('ItemCategoryId')->unique()->filter()->values();
        $allCategoryIds = collect();

        foreach ($itemCategoryIds as $catId) {
            $cat = \App\Models\Inventory\ItemCategories::find($catId);
            if ($cat) {
                $allCategoryIds->push($cat->Id);
                $parent = $cat->parent;
                while ($parent) {
                    $allCategoryIds->push($parent->Id);
                    $parent = $parent->parent;
                }
            }
        }

        // Include descendants (BFS)
        $queue = collect($itemCategoryIds);
        while ($queue->isNotEmpty()) {
            $currentBatch = $queue->splice(0, 100)->all();
            $children = DB::table('t_ItemCategories')->whereIn('ParentId', $currentBatch)->pluck('Id');
            $newChildren = $children->diff($allCategoryIds);
            if ($newChildren->isNotEmpty()) {
                $allCategoryIds = $allCategoryIds->merge($newChildren);
                $queue = $queue->merge($newChildren);
            }
        }

        $allCategoryIds = $allCategoryIds->unique()->values();

        // Prepare a subquery to fetch a single contact email per third party
        $thirdPartyUserEmailSub = DB::table('t_ThirdPartyUsers as tpu')
            ->select('tpu.ThirdPartyId', DB::raw('MIN(tpu.Email) as Email'))
            ->whereNull('tpu.DeletedOn')
            ->groupBy('tpu.ThirdPartyId');

        $suppliers = DB::table('t_SupplierMaster as sm')
            ->join('t_ThirdParties as tp', 'sm.ThirdPartyId', '=', 'tp.Id')
            ->leftJoinSub($thirdPartyUserEmailSub, 'tpu', function ($join) {
                $join->on('tpu.ThirdPartyId', '=', 'tp.Id');
            })
            ->whereNull('sm.DeletedOn')
            ->whereNull('tp.DeletedOn')
            ->where('sm.ApprovalStatus', 'A')
            ->where('sm.IsPrequalified', 1)
            ->select(
                'sm.Id',
                'sm.ThirdPartyId',
                'tp.TradingName as SupplierName',
                'tp.BusinessType',
                DB::raw('tpu.Email as Email')
            )
            ->orderBy('tp.TradingName')
            ->get();

        Log::info('Suppliers fetched for RFQ', [
            'rfq_id' => $rfq->Id,
            'supplier_count' => $suppliers->count(),
        ]);

        // Load RFQ responses with items and supplier info for printing
        $rfqResponses = RFQResponse::with(['items.uom', 'supplier.supplierMaster.party'])
            ->where('RFQId', $rfq->Id)
            ->get();

        // Get workflow data
        $canApprove = false;
        $history = collect();
        $pendingApprovals = [];

        try {
            $canApprove = $this->workflowService->canUserApprove($rfq, Auth::user());
            $history = $this->workflowService->getHistory($rfq);
            $pendingApprovals = $this->workflowService->getPendingApprovals($rfq);

            Log::info('Workflow data retrieved', [
                'rfq_id' => $rfq->Id,
                'can_approve' => $canApprove,
                'history_count' => $history->count(),
                'pending_count' => count($pendingApprovals),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get workflow data', [
                'rfq_id' => $rfq->Id,
                'error' => $e->getMessage(),
            ]);
        }

        return view('procurement.rfqs.show', compact('rfq', 'suppliers', 'rfqResponses', 'canApprove', 'history', 'pendingApprovals'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rfq = RFQ::with('suppliers')->findOrFail($id);
        $this->authorize('update', $rfq);
        $categories = ItemCategories::all();
        $suppliers = Supplier::all();

        return view('procurement.rfqs.edit', compact('rfq', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rfq = RFQ::findOrFail($id);
        $this->authorize('update', $rfq);

        $request->validate([
            'ItemCategoryId' => 'required|exists:t_ItemCategories,id',
            'Comments' => 'nullable|string|max:255',
            'SubmissionDeadline' => 'required|date|after:today',
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:t_Suppliers,Id',
        ]);

        $rfq->update([
            'ItemCategoryId' => $request->ItemCategoryId,
            'Comments' => $request->Comments,
            'SubmissionDeadline' => $request->SubmissionDeadline,
            'ModifiedBy' => Auth::user()->Id,
        ]);

        $rfq->suppliers()->sync($request->suppliers);

        return redirect()->route('rfqs.show', $rfq->Id)->with('success', 'RFQ updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $rfq = RFQ::findOrFail($id);
        $this->authorize('delete', $rfq);
        $rfq->delete();

        return redirect()->route('rfqs.index')->with('success', 'RFQ deleted successfully.');
    }

    /**
     * Get categories from requisition for RFQ line creation.
     */
    public function getRequisitionCategories($requisitionId)
    {
        try {
            $categories = DB::table('t_RequisitionLines as rl')
                ->join('t_ItemCategories as ic', 'rl.ItemCategoryId', '=', 'ic.Id')
                ->where('rl.RequisitionID', $requisitionId)
                ->whereNull('rl.DeletedOn')
                ->whereNull('ic.DeletedOn')
                ->select('ic.Id', 'ic.Name')
                ->distinct()
                ->get();

            return response()->json([
                'success' => true,
                'categories' => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch requisition categories: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories',
                'categories' => [],
            ], 500);
        }
    }

    /**
     * Returns approved requisitions that have not yet been fully covered by
     * an active (non-rejected) RFQ. Extracted to avoid duplication between
     * index() and create().
     */
    private function getAvailableRequisitions()
    {
        return DB::table('t_Requisitions as r')
            ->join('t_CodeDetails as cd', function ($join) {
                $join->on('r.DocStatus', '=', 'cd.Value')
                     ->where('cd.CodeId', '=', 'RequisitionStatus');
            })
            ->join('t_RequisitionLines as rl', 'r.Id', '=', 'rl.RequisitionID')
            ->leftJoin('t_ConsolidatedProcurementPlan as cpp', 'r.PlanRef', '=', 'cpp.PlanID')
            ->where('cd.Description', 'Approved')
            ->where('cd.IsActive', 1)
            ->whereNull('cd.DeletedOn')
            ->whereNull('r.DeletedOn')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('t_RFQLines as existing_rfql')
                    ->join('t_RFQ as existing_rfq', 'existing_rfql.RFQId', '=', 'existing_rfq.Id')
                    ->whereColumn('existing_rfql.RequisitionLineId', 'rl.Id')
                    ->whereNull('existing_rfq.DeletedOn')
                    ->whereNotIn('existing_rfq.Status', ['Re', 'Rejected', 'RE']);
            })
            ->select(
                'r.Id',
                'r.RequisitionNo',
                DB::raw("COALESCE(cpp.Title + ' - ' + cpp.ReferenceNumber, '') as PlanTitle")
            )
            ->distinct()
            ->get();
    }

    /**
     * Get workflow history manually (fallback).
     */
    private function getManualWorkflowHistory($rfqId)
    {
        return DB::table('t_WorkflowHistory as wh')
            ->join('t_Users as u', 'wh.UserId', '=', 'u.Id')
            ->where('wh.Source', 'RFQId')
            ->where('wh.SourceID', $rfqId)
            ->whereNull('wh.DeletedOn')
            ->select('wh.Action', 'u.Name as UserName', 'wh.ActionDate', 'wh.Notes')
            ->orderBy('wh.ActionDate', 'desc')
            ->get();
    }

    /**
     * Get pending approvals manually (fallback).
     */
    private function getManualPendingApprovals($rfqId)
    {
        $pending = DB::table('t_WorkflowPending as wp')
            ->join('t_WorkflowStages as ws', 'wp.StageId', '=', 'ws.Id')
            ->join('t_Users as u', 'wp.UserId', '=', 'u.Id')
            ->where('wp.Source', 'RFQId')
            ->where('wp.SourceID', $rfqId)
            ->whereNull('wp.DeletedOn')
            ->select('ws.StageName as stage_name', 'u.Name as user_name', 'wp.Status')
            ->get();

        return $pending->map(fn ($item) => (object) [
            'stage_name' => $item->stage_name,
            'user_name' => $item->user_name,
            'status' => $item->Status ?? 'Pending',
        ])->toArray();
    }
}
