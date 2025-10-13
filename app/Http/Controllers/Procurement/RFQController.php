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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // paginate RFQs 10 per page so the listing shows items 1-10 on page 1, then 11-20 on page 2, etc.
        $rfqs = RFQ::with(['category', 'suppliers', 'requisition'])->orderBy('Id', 'desc')->paginate(10);

        $requisitions = DB::table('t_Requisitions as r')
            ->join('t_CodeDetails as cd', 'r.StatusID', '=', 'cd.Id')
            ->join('t_RequisitionLines as rl', 'r.Id', '=', 'rl.RequisitionID')
            ->leftJoin('t_RFQLines as rfql', 'rl.Id', '=', 'rfql.RequisitionLineId')
            ->leftJoin('t_ConsolidatedProcurementPlan as cpp', 'r.PlanRef', '=', 'cpp.PlanID')
            ->where('cd.Description', 'Approved')
            ->whereNull('rfql.Id')
            ->select('r.Id', 'r.RequisitionNo', DB::raw("COALESCE(cpp.Title + ' - ' + cpp.ReferenceNumber, '') as PlanTitle"))
            ->distinct()
            ->get();

        return view('procurement.rfqs.index', compact('rfqs', 'requisitions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ItemCategories::all();
        $suppliers = Supplier::all(); // Fetch all suppliers for selection
        return view('procurement.rfqs.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
    public function approve(Request $request, $id)
    {
        $request->validate([
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:t_Suppliers,Id',
        ]);

        $rfq = RFQ::findOrFail($id);

        // Check if RFQ has at least one line item
        if ($rfq->rfqLines()->count() < 1) {
            return redirect()->back()->with('error', 'Cannot approve an RFQ without any items.');
        }

        // Update RFQ status to Approved
        $rfq->update(['Status' => 'Approved']);

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
            $subject = 'RFQ Approved: ' . $rfq->RFQNumber;
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

            $body = '<p>Hello ' . e($actor->Name) . ',</p>' .
                '<p>The RFQ <b>' . e($rfq->RFQNumber) . '</b> has been approved.</p>' .
                '<p>Submission Deadline: <b>' . e($submissionDeadlineFormatted) . '</b></p>' .
                '<p>Selected suppliers have been notified.</p>';

            (new UserService($actor))
                ->sendEmail($subject, $body, $cc, true, EmailPriorityEnum::Important);
        }

        return redirect()->back()->with('success', 'RFQ has been approved and emails sent to selected suppliers.');
    }

    /**
     * Reject the RFQ.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'RejectionReason' => 'required|string|max:255',
        ]);

        $rfq = RFQ::findOrFail($id);

        //Check if RFQ has at least one line item
        if ($rfq->rfqLines()->count() < 1) {
            return redirect()->back()->with('error', 'Cannot reject an RFQ without any items.');
        }
        $rfq->update([
            'Status' => 'Rejected',
            'Remarks' => $request->RejectionReason,
        ]);

        return redirect()->back()->with('success', 'RFQ has been rejected successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Get the RFQ and its associated RFQLines
        $rfq = RFQ::with('rfqLines', 'rfqLines.uom')->findOrFail($id);

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

        return view('procurement.rfqs.show', compact('rfq', 'suppliers', 'rfqResponses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rfq = RFQ::with('suppliers')->findOrFail($id);
        $categories = ItemCategories::all();
        $suppliers = Supplier::all();

        return view('procurement.rfqs.edit', compact('rfq', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'ItemCategoryId' => 'required|exists:t_ItemCategories,id',
            'Comments' => 'nullable|string|max:255',
            'SubmissionDeadline' => 'required|date|after:today',
            'suppliers' => 'required|array',
            'suppliers.*' => 'exists:t_Suppliers,Id',
        ]);

        $rfq = RFQ::findOrFail($id);

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
        $rfq->delete();

        return redirect()->route('rfqs.index')->with('success', 'RFQ deleted successfully.');
    }
}
