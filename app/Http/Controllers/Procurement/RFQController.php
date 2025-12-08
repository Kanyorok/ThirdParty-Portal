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
        // Build base query
        $query = RFQ::with(['category', 'suppliers', 'requisition']);

        // Apply filters from query string
        if ($request->filled('status')) {
            $query->where('Status', $request->query('status'));
        }
        if ($request->filled('created_by')) {
            $query->where('CreatedBy', $request->query('created_by'));
        }

        // Sorting: allow a restricted set of columns to prevent SQL injection
        $allowedSorts = [
            'RFQNumber' => 'RFQNumber',
            'Status' => 'Status',
            'SubmissionDeadline' => 'SubmissionDeadline',
            'CreatedOn' => 'CreatedOn',
            'CreatedBy' => 'CreatedBy'
        ];

        $sortBy = $request->query('sort_by');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy && isset($allowedSorts[$sortBy])) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            // default ordering
            $query->orderBy('Id', 'desc');
        }

        // paginate RFQs 10 per page, preserving query string
        $rfqs = $query->paginate(10)->withQueryString();

          
    $requisitions = DB::table('t_Requisitions as r')
        ->join('t_CodeDetails as cd', function($join){
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

        // Build CreatedBy map (Id -> Name) for users referenced by the RFQs on this page
        $createdByIds = $rfqs->pluck('CreatedBy')->unique()->filter()->values()->all();
        $createdByMap = [];
        if (!empty($createdByIds)) {
            $users = DB::table('t_Users')->whereIn('Id', $createdByIds)->select('Id', 'Name')->get();
            foreach ($users as $u) {
                $createdByMap[$u->Id] = $u->Name;
            }
        }

        // For filter dropdowns: get distinct statuses and all users (small set assumed)
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
        ->join('t_CodeDetails as cd', function($join){
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
            'SubmissionDeadline' => 'required|date|after_or_equal:today',
        ]);

        // Fetch items + quantities for this category
        // $items = DB::table('t_RequisitionLines as rl')
        //     ->join('t_Items as i', 'rl.Item', '=', 'i.id')
        //     ->where('rl.CategoryId', $request->ItemCategoryId)
        //     ->select('i.Name as name', 'rl.Quantity as quantity', 'i.UOM as uom', 'rl.Description as description')
        //     ->get();

        // Check if no items are found
        // if ($items->isEmpty()) {
        //     return redirect()->back()->with('warning', 'No items requisitioned with the chosen category.');
        // }

        // Format items for JSON storage


        $prefix = 'RFQ-';
        $lastRFQ = RFQ::where('RFQNumber', 'like', $prefix . '%')->orderBy('Id', 'desc')->first();
        $lastNumber = $lastRFQ ? intval(substr($lastRFQ->RFQNumber, strlen($prefix))) : 0;
        $newRFQNumber = $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        // Create the RFQ
        $rfq = RFQ::create([
            'RFQNumber' => $newRFQNumber,
            'RequisitionId' => $request->RequisitionId,
            'Comments' => $request->Comments,
            'SubmissionDeadline' => $request->SubmissionDeadline,
            'CreatedBy' => \Illuminate\Support\Facades\Auth::user()->Id,
            'ModifiedBy' => \Illuminate\Support\Facades\Auth::user()->Id,
            'Status' => 'Pending',
        ]);

        return redirect()->route('rfqs.show', $rfq->Id)->with('success', 'RFQ created with requisition items and sent to suppliers.');
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

        // Check if RFQ has at least one line item
        if ($rfq->rfqLines()->count() < 1) {
            return redirect()->back()->with('error', 'Cannot approve an RFQ without any items.');
        }

        // Use Workflow Service to approve
        $this->workflowService->approve($rfq, Auth::user(), 'Approved via UI');

        return redirect()->back()->with('success', 'RFQ has been approved.');
    }

    /**
     * Publish the RFQ to suppliers (Send Emails).
     */
    public function publish(Request $request, $id)
    {
        $rfq = RFQ::findOrFail($id);
        // Ensure RFQ is approved before publishing
        // We check for 'Approved' or 'Ap' or 'AP'
        if (!in_array($rfq->Status, ['Approved', 'Ap', 'AP'])) {
            return redirect()->back()->with('error', 'RFQ must be approved before publishing to suppliers.');
        }

        $request->validate([
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:t_Suppliers,Id',
        ]);

        // Ensure unique supplier IDs to avoid duplicate pivot entries
        $supplierIds = collect($request->suppliers)->map(fn($v) => (int)$v)->unique()->values()->all();

        // Update supplier statuses in the pivot table
        $rfq->suppliers()->syncWithPivotValues($supplierIds, ['Status' => 'Approved']);

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

        // Send email via UserService (actor as sender), including suppliers in CC
        $actor = Auth::user();
        if ($actor) {
            $subject = 'RFQ Invitation: ' . $rfq->RFQNumber;
            // Read SubmissionDeadline explicitly from the t_RFQ table to ensure we use the stored DB value
            $rawSubmissionDeadline = DB::table('t_RFQ')->where('Id', $rfq->Id)->value('SubmissionDeadline');
            $submissionDeadlineFormatted = 'N/A';
            if ($rawSubmissionDeadline) {
                try {
                    $submissionDeadlineFormatted = \Carbon\Carbon::parse($rawSubmissionDeadline)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // fallback to the raw value if parsing fails
                    $submissionDeadlineFormatted = $rawSubmissionDeadline;
                }
            }

            // We'll build a personalized body for each recipient inside the loop below
            $bodyTemplate = '<p>You are invited to submit a quotation for RFQ <b>' . e($rfq->RFQNumber) . '</b>.</p>' .
                '<p>Submission Deadline: <b>' . e($submissionDeadlineFormatted) . '</b></p>' .
                '<p>Please log in to the supplier portal to view details and submit your response.</p>';

            // Prepare list of unique supplier email addresses and mapping to names
            $supplierList = [];
            foreach ($cc as $entry) {
                foreach ($entry as $name => $email) {
                    $supplierList[] = ['name' => $name, 'email' => $email];
                }
            }

            // Send one email per supplier so that each supplier sees themselves in To and the others in BCC
            $uniqueEmails = array_values(array_unique(array_map(fn($s) => strtolower($s['email']), $supplierList)));
            foreach ($uniqueEmails as $idx => $recipientEmail) {
                // find display name
                $recipientName = null;
                foreach ($supplierList as $s) {
                    if (strtolower($s['email']) === $recipientEmail) {
                        $recipientName = $s['name'];
                        break;
                    }
                }

                // Build to array: only the current recipient
                $to = [[$recipientName ?? $recipientEmail => $recipientEmail]];

                // Build bcc array: all other supplier emails
                $bcc = [];
                foreach ($uniqueEmails as $otherEmail) {
                    if ($otherEmail === $recipientEmail) continue;
                    // Attempt to find name for the bcc entry
                    $otherName = null;
                    foreach ($supplierList as $s) {
                        if (strtolower($s['email']) === $otherEmail) {
                            $otherName = $s['name'];
                            break;
                        }
                    }
                    $bcc[] = [$otherName ?? $otherEmail => $otherEmail];
                }

                // Build personalized body so recipient sees their own name in the salutation
                $salutationName = $recipientName ?? $recipientEmail;
                $personalBody = '<p>Hello ' . e($salutationName) . ',</p>' . $bodyTemplate;

                // Use CRMEmailService::createRaw to persist and send the email with explicit To/BCC
                $service = \App\Services\CRMEmailService::createRaw($actor, $subject, $personalBody, $to, 'ThirdParty', '', [], $bcc, EmailPriorityEnum::Important);
                $service->send(true);
            }
        }

        return redirect()->back()->with('success', 'RFQ published and emails sent to selected suppliers.');
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


        //Check if RFQ has at least one line item
        if ($rfq->rfqLines()->count() < 1) {
            return redirect()->back()->with('error', 'Cannot reject an RFQ without any items.');
        }

        $this->workflowService->reject($rfq, Auth::user(), $request->RejectionReason);

        return redirect()->back()->with('success', 'RFQ has been rejected successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Get the RFQ and its associated RFQLines
        $rfq = RFQ::with('rfqLines', 'rfqLines.uom')->findOrFail($id);
        $this->authorize('view', $rfq);

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
            ->join('t_ThirdParties as tp', 'tp.Id', '=', 's.ThirdPartyID')
            ->leftJoinSub($thirdPartyUserEmailSub, 'tpu', function ($join) {
                $join->on('tpu.ThirdPartyId', '=', 'tp.Id');
            })
            ->whereNull('s.DeletedOn')
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

        $canApprove = $this->workflowService->canUserApprove($rfq, Auth::user());
        $history = $this->workflowService->getHistory($rfq);
        $pendingApprovals = $this->workflowService->getPendingApprovals($rfq);

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
