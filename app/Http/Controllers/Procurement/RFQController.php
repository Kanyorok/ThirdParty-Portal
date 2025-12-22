<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\RFQ;
use App\Models\ThirdParies\Supplier;
use App\Models\Procurement\RFQResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\HRM\UserService;
use App\Enums\EmailPriorityEnum;
use App\Enums\EmailTypeEnum;
use Illuminate\Support\Facades\Log;


class RFQController extends Controller
{
    public function __construct(protected \App\Services\Procurement\RFQ\RFQWorkflowService $workflowService)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
  public function index(Request $request)
{
    $this->authorize('viewAny', RFQ::class);
    
    // Build base query with explicit join to get status description
    $query = DB::table('t_RFQ as rfq')
        ->leftJoin('t_CodeDetails as cd', function($join) {
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
        'CreatedBy' => 'rfq.CreatedBy'
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


        $requisitions = DB::table('t_Requisitions as r')
            ->join('t_CodeDetails as cd', function ($join) {
                $join->on('r.DocStatus', '=', 'cd.Value')
                    ->where('cd.CodeId', '=', 'RequisitionStatus');
            })
            ->join('t_RequisitionLines as rl', 'r.Id', '=', 'rl.RequisitionID')
            ->leftJoin('t_RFQLines as rfql', 'rl.Id', '=', 'rfql.RequisitionLineId')
            ->leftJoin('t_ConsolidatedProcurementPlan as cpp', 'r.PlanRef', '=', 'cpp.PlanID')
            ->where('cd.Description', 'Approved')
            ->where('cd.IsActive', 1)
            ->whereNull('cd.DeletedOn')
            ->whereNull('r.DeletedOn') // Also check requisition not deleted
            ->whereNull('rfql.Id') // Requisition line not already in an RFQ
            ->select(
                'r.Id',
                'r.RequisitionNo',
                DB::raw("COALESCE(cpp.Title + ' - ' + cpp.ReferenceNumber, '') as PlanTitle")
            )
            ->distinct()
            ->get();

    // Build CreatedBy map
    $createdByIds = $rfqs->pluck('CreatedBy')->unique()->filter()->values()->all();
    $createdByMap = [];
    if (!empty($createdByIds)) {
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
    public function create22()
    {
        $this->authorize('create', RFQ::class);


        // Let's check what requisitions exist first
        $allRequisitions = DB::table('t_Requisitions as r')
            ->select('r.Id', 'r.RequisitionNo', 'r.DocStatus')
            ->get();

        dd([
            'all_requisitions' => $allRequisitions,
            'code_details' => DB::table('t_CodeDetails')->where('Description', 'Approved')->get(),
        ]);

        $requisitions = DB::table('t_Requisitions as r')
            ->join('t_CodeDetails as cd', 'r.DocStatus', '=', 'cd.Description') // Changed from cd.Code to cd.CodeId
            ->join('t_RequisitionLines as rl', 'r.Id', '=', 'rl.RequisitionID')
            ->leftJoin('t_RFQLines as rfql', 'rl.Id', '=', 'rfql.RequisitionLineId')
            ->leftJoin('t_ConsolidatedProcurementPlan as cpp', 'r.PlanRef', '=', 'cpp.PlanID')
            ->where('cd.Description', 'Approved')
            ->whereNull('rfql.Id') // Ensures we only get requisitions that haven't been used yet
            ->select(
                'r.Id',
                'r.RequisitionNo',
                DB::raw("COALESCE(cpp.Title + ' - ' + cpp.ReferenceNumber, '') as PlanTitle")
            )
            ->distinct()
            ->get();

        $categories = ItemCategories::all();
        $suppliers = Supplier::all();

        return view('procurement.rfqs.create', compact('categories', 'suppliers', 'requisitions'));
    }

    public function create()
    {
        $this->authorize('create', RFQ::class);

        $requisitions = DB::table('t_Requisitions as r')
            ->join('t_CodeDetails as cd', function ($join) {
                $join->on('r.DocStatus', '=', 'cd.Value')
                    ->where('cd.CodeId', '=', 'RequisitionStatus');
            })
            ->join('t_RequisitionLines as rl', 'r.Id', '=', 'rl.RequisitionID')
            ->leftJoin('t_RFQLines as rfql', 'rl.Id', '=', 'rfql.RequisitionLineId')
            ->leftJoin('t_ConsolidatedProcurementPlan as cpp', 'r.PlanRef', '=', 'cpp.PlanID')
            ->where('cd.Description', 'Approved')
            ->where('cd.IsActive', 1)
            ->whereNull('cd.DeletedOn')
            ->whereNull('r.DeletedOn') // Also check requisition not deleted
            ->whereNull('rfql.Id') // Requisition line not already in an RFQ
            ->select(
                'r.Id',
                'r.RequisitionNo',
                DB::raw("COALESCE(cpp.Title + ' - ' + cpp.ReferenceNumber, '') as PlanTitle")
            )
            ->distinct()
            ->get();

        $categories = ItemCategories::all();
        $suppliers = Supplier::all();

        return view('procurement.rfqs.create', compact('categories', 'suppliers', 'requisitions'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $this->authorize('create', RFQ::class);
    
    $request->validate([
        'RequisitionId' => 'required|exists:t_Requisitions,Id',
        'Comments' => 'nullable|string|max:255',
        'SubmissionDeadline' => [
            'required',
            'date',
            'after:today',
        ],
    ], [
        'SubmissionDeadline.after' => 'Submission deadline must be at least tomorrow.',
    ]);

    $prefix = 'RFQ-';
    $lastRFQ = RFQ::where('RFQNumber', 'like', $prefix . '%')
        ->orderBy('Id', 'desc')
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

    Log::info('Creating RFQ', [
        'rfq_number' => $newRFQNumber,
        'requisition_id' => $request->RequisitionId,
        'pending_status_value' => $pendingStatus,
        'user_id' => Auth::user()->Id
    ]);

    // Create the RFQ
    $rfq = RFQ::create([
        'RFQNumber' => $newRFQNumber,
        'RequisitionId' => $request->RequisitionId,
        'Comments' => $request->Comments,
        'SubmissionDeadline' => $request->SubmissionDeadline,
        'CreatedBy' => Auth::user()->Id,
        'ModifiedBy' => Auth::user()->Id,
        'Status' => $pendingStatus ?? 'pe', // Use Value, not Description
    ]);

    Log::info('RFQ created successfully', [
        'rfq_id' => $rfq->Id,
        'rfq_number' => $rfq->RFQNumber,
        'status' => $rfq->Status
    ]);

    return redirect()->route('rfqs.show', $rfq->Id)
        ->with('success', 'RFQ created successfully and submitted for approval.');
}

    /**
     * Approve the RFQ and notify suppliers.
     */
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
        'user_name' => Auth::user()->Name
    ]);

    // Check if RFQ has at least one line item
    if ($rfq->rfqLines()->count() < 1) {
        Log::warning('Approval blocked - no line items', [
            'rfq_id' => $rfq->Id
        ]);
        return redirect()->back()->with('error', 'Cannot approve an RFQ without any items.');
    }

    // Use Workflow Service to approve (using convenience method)
    $result = $this->workflowService->approveRFQ($rfq, Auth::user(), 'Approved via UI');
    
    // Refresh to get updated status
    $rfq->refresh();
    
    Log::info('Approval process completed', [
        'rfq_id' => $rfq->Id,
        'result' => $result,
        'new_status' => $rfq->Status,
        'status_description' => $rfq->status_description ?? 'N/A'
    ]);

    if ($result) {
        return redirect()->back()->with('success', 'RFQ has been approved successfully.');
    } else {
        return redirect()->back()->with('error', 'Failed to approve RFQ. Please check workflow configuration.');
    }
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
        'current_status' => $rfq->Status
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
        'expected_approved_value' => $approvedStatusValue
    ]);
    
    // Check if RFQ is approved (case-insensitive comparison)
    if (!$approvedStatusValue || strtolower($rfq->Status) !== strtolower($approvedStatusValue)) {
        Log::warning('Publish blocked - RFQ not approved', [
            'rfq_id' => $rfq->Id,
            'current_status' => $rfq->Status,
            'expected_status' => $approvedStatusValue
        ]);
        return redirect()->back()->with('error', 'RFQ must be approved before publishing to suppliers.');
    }

    $request->validate([
        'suppliers' => 'required|array|min:1',
        'suppliers.*' => 'exists:t_Suppliers,Id',
    ], [
        'suppliers.required' => 'Please select at least one supplier.',
        'suppliers.min' => 'Please select at least one supplier.',
    ]);

    $supplierIds = collect($request->suppliers)
        ->map(fn($v) => (int)$v)
        ->unique()
        ->values()
        ->all();

    Log::info('Publishing to suppliers', [
        'rfq_id' => $rfq->Id,
        'supplier_count' => count($supplierIds),
        'supplier_ids' => $supplierIds
    ]);

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
        'new_status' => $rfq->Status
    ]);

    // Build recipients (unique emails for selected suppliers)
    $thirdPartyUserEmailSub = DB::table('t_ThirdPartyUsers as tpu')
        ->select('tpu.ThirdPartyId', DB::raw('MIN(tpu.Email) as Email'))
        ->whereNull('tpu.DeletedOn')
        ->groupBy('tpu.ThirdPartyId');

    $recipientRows = DB::table('t_Suppliers as s')
        ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
        ->leftJoinSub($thirdPartyUserEmailSub, 'tpu', function ($join) {
            $join->on('tpu.ThirdPartyId', '=', 'tp.Id');
        })
        ->whereIn('s.Id', $supplierIds)
        ->whereNull('s.DeletedOn')
        ->whereNull('tp.DeletedOn')
        ->select('tp.TradingName', DB::raw('tpu.Email as Email'))
        ->get();

    $cc = [];
    $usedEmails = [];
    foreach ($recipientRows as $row) {
        $email = trim((string)$row->Email);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !in_array(strtolower($email), $usedEmails, true)) {
            $cc[] = [$row->TradingName => $email];
            $usedEmails[] = strtolower($email);
        }
    }

    // Send emails to suppliers
    $actor = Auth::user();
    if ($actor && !empty($cc)) {
        $subject = 'RFQ Invitation: ' . $rfq->RFQNumber;
        $submissionDeadlineFormatted = $rfq->SubmissionDeadline 
            ? \Carbon\Carbon::parse($rfq->SubmissionDeadline)->format('d/m/Y')
            : 'N/A';

        $bodyTemplate = '<p>You are invited to submit a quotation for RFQ <b>' . e($rfq->RFQNumber) . '</b>.</p>' .
            '<p>Submission Deadline: <b>' . e($submissionDeadlineFormatted) . '</b></p>' .
            '<p>Please log in to the supplier portal to view details and submit your response.</p>';

        // Prepare supplier list
        $supplierList = [];
        foreach ($cc as $entry) {
            foreach ($entry as $name => $email) {
                $supplierList[] = ['name' => $name, 'email' => $email];
            }
        }

        // Send one email per supplier
        $uniqueEmails = array_values(array_unique(array_map(fn($s) => strtolower($s['email']), $supplierList)));
        foreach ($uniqueEmails as $recipientEmail) {
            $recipientName = null;
            foreach ($supplierList as $s) {
                if (strtolower($s['email']) === $recipientEmail) {
                    $recipientName = $s['name'];
                    break;
                }
            }

            $to = [[$recipientName ?? $recipientEmail => $recipientEmail]];

            // Build BCC with other suppliers
            $bcc = [];
            foreach ($uniqueEmails as $otherEmail) {
                if ($otherEmail === $recipientEmail) continue;
                $otherName = null;
                foreach ($supplierList as $s) {
                    if (strtolower($s['email']) === $otherEmail) {
                        $otherName = $s['name'];
                        break;
                    }
                }
                $bcc[] = [$otherName ?? $otherEmail => $otherEmail];
            }

            $salutationName = $recipientName ?? $recipientEmail;
            $personalBody = '<p>Hello ' . e($salutationName) . ',</p>' . $bodyTemplate;

            try {
                $service = \App\Services\CRMEmailService::createRaw(
                    $actor, 
                    $subject, 
                    $personalBody, 
                    $to, 
                    'ThirdParty', 
                    '', 
                    [], 
                    $bcc, 
                    \App\Enums\EmailPriorityEnum::Important
                );
                $service->send(true);
                
                Log::info('Email sent to supplier', [
                    'rfq_id' => $rfq->Id,
                    'recipient' => $recipientEmail
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send email to supplier', [
                    'rfq_id' => $rfq->Id,
                    'recipient' => $recipientEmail,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    return redirect()->back()->with('success', 'RFQ published and invitation emails sent to ' . count($supplierIds) . ' supplier(s).');
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
        'reason' => $request->RejectionReason
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
        'result' => $result
    ]);

    if ($result) {
        return redirect()->back()->with('success', 'RFQ has been rejected successfully.');
    } else {
        return redirect()->back()->with('error', 'Failed to reject RFQ.');
    }
}

    /**
     * Display the specified resource.
     */ 
  public function show($id)
{
    // Get the RFQ and its associated RFQLines
    $rfq = RFQ::with([
        'rfqlines',
        'rfqLines.uom', 
        'requisition', 
        'workflowHistory.creator', // Loads the user who took action
        'workflowHistory.status',  // Loads the status description
        'workflowPending.user'     // Loads who the approval is waiting on
    ])->findOrFail($id);

    $this->authorize('view', $rfq);

    // Debug: Check what's being passed to workflow service
    Log::info('RFQ Show - Workflow Debug', [
        'rfq_id' => $rfq->Id,
        'rfq_number' => $rfq->RFQNumber,
        'status' => $rfq->Status,
        'source_alias' => RFQ::getPrimaryKey()
    ]);

    // Gather item category IDs from RFQ lines and include ancestors and descendants
    $itemCategoryIds = $rfq->rfqLines->pluck('ItemCategoryId')->unique()->filter()->values();
    $allCategoryIds = collect();
    foreach ($itemCategoryIds as $catId) {
        $cat = \App\Models\Inventory\ItemCategories::find($catId);
        if ($cat) {
            $allCategoryIds->push($cat->Id);
            // include ancestors so if classification includes a parent, subcategory items still qualify
            $parent = $cat->parent;
            while ($parent) {
                $allCategoryIds->push($parent->Id);
                $parent = $parent->parent;
            }
        }
    }

    // include descendants (BFS) so if a parent item category is mapped, its subcategories are covered
    $queue = collect($itemCategoryIds);
    while ($queue->isNotEmpty()) {
        $currentBatch = $queue->splice(0, 100)->all();
        $children = DB::table('t_ItemCategories')
            ->whereIn('ParentId', $currentBatch)
            ->pluck('Id');
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

        // Select suppliers and de-duplicate by ThirdPartyId (one row per supplier in UI)
        $suppliers = DB::table('t_Suppliers as s')
            ->join('t_SupplierMaster as sm', 'sm.Id', '=', 's.SupplierMasterId')
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 'sm.ThirdPartyId')
            ->leftJoinSub($thirdPartyUserEmailSub, 'tpu', function ($join) {
                $join->on('tpu.ThirdPartyId', '=', 'tp.Id');
            })
            ->whereNull('s.DeletedOn')
            ->whereNull('sm.DeletedOn')
            ->whereNull('tp.DeletedOn')
            ->where('s.Active_Status', 1)
            ->whereExists(function ($q) use ($allCategoryIds) {
                $q->select(DB::raw(1))
                    ->from('t_SupplierCategory_ItemCategory as scic')
                    ->join('t_SupplierCategories as sc', 'sc.SupplierCategoryID', '=', 'scic.SupplierCategoryID')
                    ->whereNull('sc.DeletedOn')
                    ->whereNull('scic.DeletedOn')
                    ->whereIn('scic.ItemCategoryID', $allCategoryIds)
                    ->whereColumn('sc.SupplierCategoryID', 's.CategoryId');
            })
            ->groupBy('tp.Id', 'tp.TradingName', 'tp.BusinessType')
            ->select(
                DB::raw('MIN(s.Id) as Id'),
                DB::raw('MIN(s.CategoryId) as SupplierCategoryId'),
                'tp.Id as ThirdPartyId',
                'tp.TradingName as SupplierName',
                'tp.BusinessType',
                DB::raw('MIN(tpu.Email) as Email')
            )
            ->get();

    // Load RFQ responses (supplier quotations) with items and supplier info for printing
    $rfqResponses = RFQResponse::with(['items.uom', 'supplier.thirdParty'])
        ->where('RFQId', $rfq->Id)
        ->get();

    // Get workflow data
try {
        $canApprove = $this->workflowService->canUserApprove($rfq, Auth::user());
        
        // Use the relationships we just defined and eager-loaded
        $history = $rfq->workflowHistory; 
        $pendingApprovals = $rfq->workflowPending;

        Log::info('Workflow data retrieved via Eloquent', [
            'rfq_id' => $rfq->Id,
            'history_count' => $history->count(),
            'pending_count' => $pendingApprovals->count()
        ]);
    } catch (\Exception $e) {
        Log::error('Workflow service error: ' . $e->getMessage());
        $canApprove = false;
        $history = collect();
        $pendingApprovals = collect();
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


        // Update RFQ details
        $rfq->update([
            'ItemCategoryId' => $request->ItemCategoryId,
            'Comments' => $request->Comments,
            'SubmissionDeadline' => $request->SubmissionDeadline,
            'ModifiedBy' => \Illuminate\Support\Facades\Auth::user()->Id,
        ]);

        // Update suppliers in the pivot table
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
     * Get categories from requisition for RFQ line creation
     * Add this method to your RFQController
     */
    public function getRequisitionCategories($requisitionId)
    {
        try {
            // Get distinct item categories from requisition lines
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
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch requisition categories: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories',
                'categories' => []
            ], 500);
        }
    }
}
