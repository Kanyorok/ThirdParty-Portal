<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\CRM\Schedule;
use App\Models\CRM\ScheduleUser;
use App\Models\Legal\LegalObligation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LegalObligationController extends Controller
{
    public function index()
    {
        $obligations = LegalObligation::all();
        $details = CodeDetail::select('Value')
            ->where('CodeID','LegalSourceTypes')
            ->get();
            
        return view('legal.obligations.index', compact('obligations', 'details'));
    }

    public function create()
    {

        $details = CodeDetail::select('Value')
            ->where('CodeID','LegalSourceTypes')
            ->get();
        return view('legal.obligations.create', compact('details'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'SourceType' => 'required|exists:t_CodeDetails,Value',
            'DueDate' => 'required|date',
            'Description' => 'nullable|string',
        ]);

        $obligations = LegalObligation::create([
            'Title'=> $validated['Title'],
            'SourceType'=> $validated['SourceType'],
            'DueDate'=> $validated['DueDate'],
            'Status'=> $validated['Status'] ?? 'pending',
            'Description'=> $validated['Description'],
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        return redirect()->route('legal.obligations.index')->with('success', 'Obligation created successfully.');
    }

    public function edit($id)
    {
        $obligation = LegalObligation::findOrFail($id);
        return view('legal.obligations.edit', compact('obligation'));
    }

    public function update(Request $request, $id)
    {
        $obligation = LegalObligation::findOrFail($id);

        $data = $request->validate([
            'Title' => 'required|string|max:255',
            'SourceType' => 'required|in:Contract,Case',
            'DueDate' => 'required|date',
            'Status' => 'required|string',
            'Description' => 'nullable|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        $obligation->update($data);

        return redirect()->route('legal.obligations.index')->with('success', 'Obligation updated successfully.');
    }

    public function show($id)
    {
        $obligation = LegalObligation::findOrFail($id);
        $users = User::select('Name', 'Id', 'Email')
            ->get();
        return view('legal.obligations.show', compact('obligation', 'users'));
    }

    public function getObligations($id)
    {
        $obligations = LegalObligation::findOrFail($id);
        return response()->json($obligations);
    }

    public function destroy($id)
    {
        $obligation = LegalObligation::findOrFail($id);
        $obligation->DeletedBy = Auth::id();
        $obligation->save();
        $obligation->delete();
        return back()->with('success','Obligation successfully deleted');
    }

    public function assignUser(Request $request, $id)
    {
        $obligation = LegalObligation::with('users:Id,Name,Email')->findOrFail($id);
        $validated = $request->validate([
            'UserId' => 'required|exists:t_Users,Id',
        ]);
        // Check if the user is already assigned
        if ($obligation->AssignedTo == $validated['UserId']) {
            return back()->withErrors('Error', 'User Already Assigned');
        }
        else{

        //Store in scheduled table
        $schedule = Schedule::create([
            'Title' => $obligation->Title,
            'Notes' => $obligation->Description,
            // 'ScheduledTypeID' => $obligation->Id,
            'ScheduleStatusID' => 'sc',
            'StartOn' => $obligation->DueDate,
            'EndOn' => $obligation->DueDate,
            'Type' => 'Legal',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        $userschedule = ScheduleUser::create([
            'ScheduleId' => $schedule->ScheduleID,
            'UserID' => $validated['UserId'],
            'ScheduleUserStatus' => 'ac',
            'DecidedOn' => now(),
            'ReminderOn' => $obligation->DueDate, // Example reminder 7 days before due date
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        $obligation->update([
            'AssignedTo' => $validated['UserId'],
            'ScheduledID' => $obligation->ScheduleID,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.obligations.show', $id)->with('success', 'User assigned successfully.');
    }
    }

    // public function markComplete($id)
    // {
    //     $obligation = LegalObligation::findOrFail($id);
    //     $obligation->Status = 'Completed';
    //     $obligation->ModifiedBy = Auth::id();
    //     $obligation->ModifiedOn = now();
    //     $obligation->save();

    //     return redirect()->back()->with('success', 'Obligation marked as completed.');
    // }



    // public function calendar()
    // {
    //     $obligations = LegalObligation::whereNull('DeletedOn')->get();

    //     $calendarEvents = $obligations->map(function ($obligation) {
    //         return [
    //             'title' => $obligation->ObligationTitle,
    //             'start' => $obligation->DueDate,
    //             'url' => route('legal.obligations.show', $obligation->ID),
    //         ];
    //     });

    //     return view('legal.obligations.calendar', compact('calendarEvents'));
    // }
}
