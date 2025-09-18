<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\TenderCommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Procurement\RFQCommitteeMember;

class TenderAcceptController extends Controller
{
    //
        public function index()
    {
        // Resolve the current employee identifier that is stored in UserID column
        $employeeId = optional(Auth::user())->EmployeeId ?? optional(Auth::user()?->employee)->Id;

        $tenders = TenderCommitteeMember::where('UserID', $employeeId)
            ->where(function($q){ $q->whereNull('Response')->orWhere('Response', 0); })
            ->with(['tender', 'createdBy'])
            ->get();

        $rfq = RFQCommitteeMember::where('UserID', $employeeId)
            ->where(function($q){ $q->whereNull('Response')->orWhere('Response', 0); })
            ->with(['rfq', 'createdBy'])
            ->get();

        return view('procurement.tendering.bidopeningandevaluation.memberresponse.index', compact('tenders','rfq'));
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.memberresponse.create');
    }

   public function store(Request $request)
{

    try {
        DB::beginTransaction();

        // Match records by employee identifier used in committee tables
        $employeeId = optional(Auth::user())->EmployeeId ?? optional(Auth::user()?->employee)->Id;

        // Validate response input to only accept 1 (accept) or 2 (decline)
        $validated = $request->validate([
            'tender_id' => 'nullable|exists:t_Tender,Id',
            'tender_response' => 'nullable|in:1,2',
            'tender_comments' => 'nullable|string|max:1000',
            'rfq_id' => 'nullable|exists:t_RFQ,Id',
            'rfq_response' => 'nullable|in:1,2',
            'rfq_comments' => 'nullable|string|max:1000',
        ]);

        // Update tender committee response if submitted
        if ($request->filled('tender_id') && $request->filled('tender_response')) {
            TenderCommitteeMember::where('UserID', $employeeId)
                ->where('TenderID', $request->tender_id)
                ->update([
                    'Response' => (int)$request->tender_response,
                    'reason' => $request->tender_comments,
                ]);
        }

        // Update RFQ committee response if submitted
        if ($request->filled('rfq_id') && $request->filled('rfq_response')) {
            RFQCommitteeMember::where('UserID', $employeeId)
                ->where('RFQID', $request->rfq_id)
                ->update([
                    'Response' => (int)$request->rfq_response,
                    'reason' => $request->rfq_comments,
                ]);
        }

        DB::commit();
        return back()->with('success', 'Response saved successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', 'An error occurred. Please try again later.');
        }
    }

}
