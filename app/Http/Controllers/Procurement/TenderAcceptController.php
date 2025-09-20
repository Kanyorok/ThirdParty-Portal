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
        // Get the current user ID (not EmployeeId) as committee members are stored by User ID
        $currentUserId = Auth::id();

        // Match records stored either with User.Id or Employee.Id
        $tenders = TenderCommitteeMember::where(function($q) use ($currentUserId) {
                $q->where('UserID', $currentUserId)
                  ->orWhereHas('user', fn($uq) => $uq->where('Id', $currentUserId))
                  ->orWhereHas('userByEmployee', fn($uq) => $uq->where('Id', $currentUserId));
            })
            ->where(function($q){ $q->whereNull('Response')->orWhere('Response', 0); })
            ->with(['tender', 'createdBy'])
            ->get();

        $rfq = RFQCommitteeMember::where('UserID', $currentUserId)
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

        // Get current user ID to match committee records
        $currentUserId = Auth::id();

        // Validate response input to only accept 1 (accept) or 2 (decline)
        $validated = $request->validate([
            'tender_id' => 'nullable|exists:t_Tenders,Id',
            'tender_response' => 'nullable|in:1,2',
            'tender_comments' => 'nullable|string|max:1000',
            'rfq_id' => 'nullable|exists:t_RFQ,Id',
            'rfq_response' => 'nullable|in:1,2',
            'rfq_comments' => 'nullable|string|max:1000',
        ]);

        // Update tender committee response if submitted
        if ($request->filled('tender_id') && $request->filled('tender_response')) {
            TenderCommitteeMember::where('UserID', $currentUserId)
                ->where('TenderID', $request->tender_id)
                ->update([
                    'Response' => (int)$request->tender_response,
                    'reason' => $request->tender_comments,
                    'ModifiedBy' => $currentUserId,
                    'ModifiedOn' => now(),
                ]);
        }

        // Update RFQ committee response if submitted
        if ($request->filled('rfq_id') && $request->filled('rfq_response')) {
            RFQCommitteeMember::where('UserID', $currentUserId)
                ->where('RFQID', $request->rfq_id)
                ->update([
                    'Response' => (int)$request->rfq_response,
                    'reason' => $request->rfq_comments,
                    'ModifiedBy' => $currentUserId,
                    'ModifiedOn' => now(),
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
