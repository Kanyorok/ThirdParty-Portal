<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\HRM\Employee;
use App\Models\Procurement\RFQ;
use App\Models\Procurement\RFQCommittee;
use App\Models\Procurement\RFQCommitteeMember;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCommittee;
use App\Models\Procurement\TenderCommitteeMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderCommitteeController extends Controller
{
    public function index(Request $request)
    {
        // Tender Committees
        $tenderCommittees = TenderCommittee::with('tender')->withCount('members')->get()->map(function ($item) {
            return [
                'id' => $item->Id ?? $item->TenderID,
                'type' => 'tender',
                'ref' => $item->tender->TenderNo ?? 'N/A',
                'refId' => $item->TenderID,
                'members_count' => $item->members_count,
                'appointment_date' => $item->AppointmentDate,
            ];
        });

        // RFQ Committees
        $rfqCommittees = RFQCommittee::with('rfq')->withCount('members')->get()->map(function ($item) {
            return [
                'id' => $item->Id ?? $item->RFQID,
                'type' => 'rfq',
                'ref' => $item->rfq->RFQNumber ?? 'N/A',
                'refId' => $item->RFQID,
                'members_count' => $item->members_count,
                'appointment_date' => $item->AppointmentDate,
            ];
        });
        //dd($rfqCommittees);
        // Combine both and sort
        $committeesCollection = collect($tenderCommittees)
            ->merge($rfqCommittees)
            ->sortByDesc('appointment_date')
            ->values();

        // Pagination: convert collection to LengthAwarePaginator
        $perPage = 10;
        $page = (int) $request->query('page', 1);
        $total = $committeesCollection->count();
        $currentPageItems = $committeesCollection->forPage($page, $perPage)->values();
        $committees = new LengthAwarePaginator($currentPageItems, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        // All employees (used in modal)
        $employees = Employee::select('Id', 'EmployeeID', 'FirstName', 'LastName', 'JobTitle')->get();

        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.index', compact(
            'committees',
            'employees'
        ));
    }

    public function create()
    {
        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.create');
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'committeeType' => 'required|in:tender,rfq',
            'referenceId' => 'required|integer',
            'appointmentDate' => 'required|date',
            'committeeMembers' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $now = Carbon::now();

            $committeeType = $request->committeeType;
            $referenceId = $request->referenceId;

            // Build committee name and optional legacy TenderID
            $committeeName = ucfirst($committeeType) . ' Committee for #' . $referenceId;
            $tenderId = $committeeType === 'tender' ? $referenceId : null;

            // ✅ Create committee record
            $committee = TenderCommittee::create([
                'CommitteeType' => $committeeType,
                'ReferenceId' => $referenceId,
                'TenderID' => $tenderId, // 🔁 Maintain legacy TenderID
                'CommitteeName' => $committeeName,
                'AppointmentDate' => $request->appointmentDate,
                'IsActive' => true,
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
            ]);

            // ✅ Create members
            foreach ($request->committeeMembers as $memberId) {
                TenderCommitteeMember::create([
                    'CommitteeID' => $committee->Id,
                    'UserID' => $memberId,
                    'TenderID' => $tenderId, // 🔁 Keep for legacy linkage
                    'Role' => 'Member',
                    'Response' => 0,
                    'IsActive' => true,
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
                    'committeeType' => $committeeType,
                    'referenceId' => $referenceId,
                ])
                ->log('Committee and members appointed successfully.');

            return redirect()->back()->with('success', ucfirst($committeeType) . ' Committee successfully appointed.');
        } catch (\Throwable $th) {
            DB::rollBack();

            $activity = activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'committeeType' => $request->committeeType,
                    'referenceId' => $request->referenceId,
                ]);

            if (isset($committee)) {
                $activity->performedOn($committee);
            }

            $activity->log('Failed to appoint Committee: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to appoint Committee: ' . $th->getMessage());
        }
    }

    public function show($id, $type)
    {
        $committeeMembers = collect();
        $title = null;

        if ($type === 'tender') {
            $tender = Tender::find($id);
            if (! $tender) {
                return redirect()->back()->with('error', 'Tender not found with ID: ' . $id);
            }
            $title = $tender->Title;
            $committeeMembers = TenderCommitteeMember::where('TenderID', $id)
                ->with(['user.employee'])
                ->get();
        } elseif ($type === 'rfq') {
            $rfq = RFQ::find($id);
            if (! $rfq) {
                return redirect()->back()->with('error', 'RFQ not found with ID: ' . $id);
            }
            $title = $rfq->RFQNumber;
            $committeeMembers = RFQCommitteeMember::where('RFQID', $id)
                ->with(['user.employee'])
                ->get();
        }

        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.TenderMembers', [
            'committeeMembers' => $committeeMembers,
            'tenderTitle' => $title,
            'tenderID' => $id,
            'committeeType' => $type,
        ]);
    }

    public function membersAdd(Request $request)
    {
        $request->validate([
            'tenderID' => 'required',
            'memberID' => 'required|array',
            'memberRole' => 'required|array',
            'committeeType' => 'required|in:tender,rfq',
        ]);

        $now = Carbon::now();
        $userId = Auth::id();

        DB::beginTransaction();

        try {
            foreach ($request->memberID as $index => $userID) {
                $role = $request->memberRole[$index];

                if ($request->committeeType === 'tender') {
                    TenderCommitteeMember::where('TenderID', $request->tenderID)
                        ->where('UserID', $userID)
                        ->update([
                            'Role' => $role,
                            'ModifiedBy' => $userId,
                            'ModifiedOn' => $now,
                        ]);
                } elseif ($request->committeeType === 'rfq') {
                    \App\Models\Procurement\RFQCommitteeMember::where('RFQID', $request->tenderID)
                        ->where('UserID', $userID)
                        ->update([
                            'Role' => $role,
                            'ModifiedBy' => $userId,
                            'ModifiedOn' => $now,
                        ]);
                }
            }

            DB::commit();
            activity()
                ->causedBy(Auth::user())
                ->withProperties(['tenderID' => $request->tenderID])
                ->log('Committee roles updated successfully.');

            return redirect()->back()->with('success', 'Committee roles updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->causedBy(Auth::user())
                ->withProperties(['tenderID' => $request->tenderID])
                ->log('Failed to update committee roles: ' . $th->getMessage());

            return redirect()->back()->with('error', 'Failed to update committee roles: ' . $th->getMessage());
        }
    }

    public function getReferences($type)
    {
        if ($type === 'tender') {
            $data = Tender::select('Id', 'TenderNo as RefNo', 'Title')->get();
        } elseif ($type === 'rfq') {
            $data = RFQ::select('Id', 'RFQNumber as RefNo')->get();
        } else {
            return response()->json([], 400);
        }

        return response()->json($data);
    }
}
