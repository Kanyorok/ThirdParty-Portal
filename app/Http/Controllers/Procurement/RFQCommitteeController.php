<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\RFQCommittee;
use App\Models\Procurement\RFQCommitteeMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RFQCommitteeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'committeeType' => 'required|in:rfq',
            'referenceId' => 'required|integer',
            'appointmentDate' => 'required|date',
            'committeeMembers' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $userId = Auth::id();
            $now = Carbon::now();

            $committeeName = 'RFQ Committee for #' . $request->referenceId;

            // Create RFQ Committee
            $committee = RFQCommittee::create([
                'RFQID' => $request->referenceId,
                'CommitteeName' => $committeeName,
                'AppointmentDate' => $request->appointmentDate,
                'IsActive' => true,
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
            ]);

            // Add Members
            foreach ($request->committeeMembers as $memberId) {
                RFQCommitteeMember::create([
                    'CommitteeID' => $committee->Id,
                    'UserID' => $memberId,
                    'RFQID' => $request->referenceId,
                    'Role' => 'Member',
                    'Response' => 0,
                    'IsActive' => true,
                    'HasEvaluated' => false,
                    'CreatedBy' => $userId,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => $now,
                ]);
            }

            DB::commit();

            activity()
                ->performedOn($committee)
                ->causedBy(Auth::user())
                ->withProperties([
                    'committeeType' => 'rfq',
                    'referenceId' => $request->referenceId,
                ])
                ->log('RFQ Committee and members appointed successfully.');

            return redirect()->back()->with('success', 'RFQ Committee successfully appointed.');
        } catch (\Throwable $th) {
            DB::rollBack();

            $activity = activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'committeeType' => 'rfq',
                    'referenceId' => $request->referenceId,
                ]);

            if (isset($committee)) {
                $activity->performedOn($committee);
            }

            $activity->log('Failed to appoint RFQ Committee: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to appoint RFQ Committee: ' . $th->getMessage());
        }
    }
}
