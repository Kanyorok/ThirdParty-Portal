<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\HRM\Employee;
use App\Models\Procurement\Tender;
use App\Models\Procurement\TenderCommittee;
use App\Models\Procurement\TenderCommitteeMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TenderCommitteeController extends Controller
{
    //

        public function index()
    {
        //Fetch all tenders that are already in the committee and get member count
        $tenderCommittees = TenderCommittee::with('tender')->withCount('members')->get();
        
        $tenderIds = $tenderCommittees->pluck('TenderID')->toArray();
        //Fetch All tenders not in the committee
        $tenders = Tender::whereNotIn('Id', $tenderIds)->get();

        //Fetch All Employees to be added to the committee
        $employees =Employee::select('Id','EmployeeID','FirstName','LastName','JobTitle')->get();

        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.index',compact(
            'tenderCommittees',
            'tenders',
            'employees'
        ));
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.create');
    }


    public function store(Request $request)
    {
        //Check if user is authorized to create a tender committee
        //$this->authorize('create', TenderCommittee::class);
        // 1. Validate the input
        $request->validate([
            'tenderID' => 'required|integer|exists:t_Tenders,Id',
            'appointmentDate' => 'required|date',
            'committeeMembers' => 'required|array|min:1',
        ]);
        DB::beginTransaction();
        try {
            $committee = TenderCommittee::create([
                'TenderID' => $request->tenderID,
                'CommitteeName' => 'Tender Committee for Tender #' . $request->tenderID,
                'AppointmentDate' => $request->appointmentDate,
                'IsActive' => true,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => Carbon::now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => Carbon::now(),
            ]);
            
            $now = Carbon::now();
            $userId = Auth::id();
            foreach ($request->committeeMembers as $memberId) {
                TenderCommitteeMember::create([
                'CommitteeID' => $committee->Id,
                'UserID' => $memberId,
                'TenderID' => $request->tenderID,
                'Role' => 'Member', // or fetch actual role if you support it
                'Response' => 0, // default to 'Pending'
                'IsActive' => true,
                'CreatedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
                ]);
            }
            DB::commit();
            //Log the action
            activity()
                ->performedOn($committee)
                ->causedBy(Auth::user())
                ->withProperties(['tenderID' => $request->tenderID])
                ->log('Tender Committee and members appointed successfully.');
            return redirect()->back()->with('success', 'Tender Committee and members successfully appointed.');
        } catch (\Throwable $th) {
            DB::rollBack();
            //Log the error
            activity()
                ->performedOn($committee ?? null)
                ->causedBy(Auth::user())
                ->withProperties(['tenderID' => $request->tenderID])
                ->log('Failed to appoint Tender Committee and members: ' . $th->getMessage());
            return redirect()->back()->with('error', 'Failed to appoint Tender Committee and members: ' . $th->getMessage());
        }

    }


    public function show($id)
    {
        //Fetch the tender committee by ID
        // /return$tenderCommittee = TenderCommittee::with('tender', 'members')->findOrFail($id);
        $tenderTitle = Tender::findOrFail($id)->Title;
        $tenderID=$id;
        //Fetch all members of the committee
        $committeeMembers = TenderCommitteeMember::where('TenderID', $id)->with('employee')->get();

        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.TenderMembers', compact(
            'committeeMembers',
            'tenderTitle',
            'tenderID'
        ));
    }

    public function membersAdd(Request $request){

    $request->validate([
        'tenderID' => 'required',
        'memberID' => 'required|array',
        'memberRole' => 'required|array',
    ]);

    $now = Carbon::now();
    $userId = Auth::id();

    DB::beginTransaction();
    try {
        foreach ($request->memberID as $index => $userID) {
        $role = $request->memberRole[$index];

        TenderCommitteeMember::where('TenderID', $request->tenderID)
            ->where('UserID', $userID)
            ->update([
                'Role' => $role,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now
            ]);
    }

    DB::commit();
        //Log the action
        activity()
            ->causedBy(Auth::user())
            ->withProperties(['tenderID' => $request->tenderID])
            ->log('Committee roles updated successfully.');
    return redirect()->back()->with('success', 'Committee roles updated successfully.');
    } catch (\Throwable $th) {
        DB::rollBack();
        //Log the error
        activity()
            ->causedBy(Auth::user())
            ->withProperties(['tenderID' => $request->tenderID])
            ->log('Failed to update committee roles: ' . $th->getMessage());
        return redirect()->back()->with('error', 'Failed to update committee roles: ' . $th->getMessage());
    }
    }

}
