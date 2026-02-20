<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\CommitteeRoleHistory;
use App\Models\Procurement\RFQCommitteeMember;
use App\Models\Procurement\TenderCommitteeMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderAcceptController extends Controller
{
    public function index()
    {
        $currentUserId = Auth::id();
        $currentEmployeeId = optional(Auth::user())->EmployeeId;

        // Tenders: pending response (Response=0/null) OR pending role change
        $tenders = TenderCommitteeMember::where(function ($q) use ($currentUserId, $currentEmployeeId) {
            $q->where('UserID', $currentUserId);
            if ($currentEmployeeId) {
                $q->orWhere('UserID', $currentEmployeeId);
            }
        })
            ->whereNotNull('TenderID')
            ->whereHas('tender')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('Response')->orWhere('Response', 0);
                })
                ->orWhereNotNull('PendingRole');
            })
            ->with(['tender:Id,TenderNo,Title', 'createdBy:Id,Name'])
            ->get();

        // RFQs: pending response (Response=0/null) OR pending role change
        $rfq = RFQCommitteeMember::where(function ($q) use ($currentUserId, $currentEmployeeId) {
            $q->where('UserID', $currentUserId);
            if ($currentEmployeeId) {
                $q->orWhere('UserID', $currentEmployeeId);
            }
        })
            ->whereNotNull('RFQID')
            ->whereHas('rfq')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('Response')->orWhere('Response', 0);
                })
                ->orWhereNotNull('PendingRole');
            })
            ->with(['rfq:Id,RFQNumber', 'createdBy:Id,Name'])
            ->get();

        return view('procurement.tendering.bidopeningandevaluation.memberresponse.index', compact('tenders', 'rfq'));
    }

    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.memberresponse.create');
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $currentUserId = Auth::id();
            $currentEmployeeId = optional(Auth::user())->EmployeeId;

            $validated = $request->validate([
                'tender_id'       => 'nullable|exists:t_Tenders,Id',
                'tender_response' => 'nullable|in:1,2',
                'tender_comments' => 'nullable|string|max:1000',
                'rfq_id'          => 'nullable|exists:t_RFQ,Id',
                'rfq_response'    => 'nullable|in:1,2',
                'rfq_comments'    => 'nullable|string|max:1000',
            ]);

            // --- Tender response ---
            if ($request->filled('tender_id') && $request->filled('tender_response')) {
                $tenderMember = TenderCommitteeMember::where(function ($q) use ($currentUserId) {
                    $q->where('UserID', $currentUserId)
                        ->orWhereHas('user', fn ($uq) => $uq->where('Id', $currentUserId))
                        ->orWhereHas('userByEmployee', fn ($uq) => $uq->where('Id', $currentUserId));
                })
                    ->where('TenderID', $request->tender_id)
                    ->first();

                if ($tenderMember) {
                    $response = (int) $request->tender_response;

                    $updateData = [
                        'Response'   => $response,
                        'reason'     => $request->tender_comments,
                        'ModifiedBy' => $currentUserId,
                        'ModifiedOn' => now(),
                    ];

                    // Handle pending role change
                    if ($tenderMember->PendingRole) {
                        if ($response === 1) {
                            // Accept: apply the pending role
                            $updateData['Role']        = $tenderMember->PendingRole;
                            $updateData['PendingRole'] = null;
                        } else {
                            // Decline: discard pending role, keep current
                            $updateData['PendingRole'] = null;
                        }

                        // Update the history record
                        $historyStatus = $response === 1
                            ? CommitteeRoleHistory::STATUS_ACCEPTED
                            : CommitteeRoleHistory::STATUS_DECLINED;

                        CommitteeRoleHistory::where('MemberType', 'tender')
                            ->where('MemberID', $tenderMember->id)
                            ->where('Status', CommitteeRoleHistory::STATUS_PENDING)
                            ->orderByDesc('ChangedOn')
                            ->limit(1)
                            ->update([
                                'Status'      => $historyStatus,
                                'RespondedOn' => now(),
                                'ModifiedOn'  => now(),
                            ]);
                    }

                    $tenderMember->update($updateData);
                }
            }

            // --- RFQ response ---
            if ($request->filled('rfq_id') && $request->filled('rfq_response')) {
                $rfqMember = RFQCommitteeMember::where(function ($q) use ($currentUserId, $currentEmployeeId) {
                    $q->where('UserID', $currentUserId)
                        ->orWhereHas('user', fn ($uq) => $uq->where('Id', $currentUserId))
                        ->orWhereHas('userByEmployee', fn ($uq) => $uq->where('Id', $currentUserId));

                    if ($currentEmployeeId) {
                        $q->orWhere('UserID', $currentEmployeeId);
                    }
                })
                    ->where('RFQID', $request->rfq_id)
                    ->first();

                if ($rfqMember) {
                    $response = (int) $request->rfq_response;

                    $updateData = [
                        'Response'   => $response,
                        'reason'     => $request->rfq_comments,
                        'ModifiedBy' => $currentUserId,
                        'ModifiedOn' => now(),
                    ];

                    // Handle pending role change
                    if ($rfqMember->PendingRole) {
                        if ($response === 1) {
                            $updateData['Role']        = $rfqMember->PendingRole;
                            $updateData['PendingRole'] = null;
                        } else {
                            $updateData['PendingRole'] = null;
                        }

                        $historyStatus = $response === 1
                            ? CommitteeRoleHistory::STATUS_ACCEPTED
                            : CommitteeRoleHistory::STATUS_DECLINED;

                        CommitteeRoleHistory::where('MemberType', 'rfq')
                            ->where('MemberID', $rfqMember->id)
                            ->where('Status', CommitteeRoleHistory::STATUS_PENDING)
                            ->orderByDesc('ChangedOn')
                            ->limit(1)
                            ->update([
                                'Status'      => $historyStatus,
                                'RespondedOn' => now(),
                                'ModifiedOn'  => now(),
                            ]);
                    }

                    $rfqMember->update($updateData);
                }
            }

            DB::commit();

            return back()->with('success', 'Response saved successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()->with('error', 'An error occurred. Please try again later.');
        }
    }
}
