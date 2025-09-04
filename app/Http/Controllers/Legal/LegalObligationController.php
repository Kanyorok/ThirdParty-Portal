<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\CRM\Schedule;
use App\Models\CRM\ScheduleUser;
use App\Models\Legal\LegalObligation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegalObligationController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::LegalObligationView, LegalObligation::class);

        $obligations = LegalObligation::all();
        $details = CodeDetail::select('Value')
            ->where('CodeID','LegalSourceTypes')
            ->get();

        return view('legal.obligations.index', compact('obligations', 'details'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::LegalObligationCreate, LegalObligation::class);

        $details = CodeDetail::select('Value')
            ->where('CodeID','LegalSourceTypes')
            ->get();
        return view('legal.obligations.create', compact('details'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::LegalObligationCreate, LegalObligation::class);

        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'SourceType' => 'required|exists:t_CodeDetails,Value',
            'DueDate' => 'required|date',
            'Description' => 'required|string',
        ]);

        $duplicate = LegalObligation::where('Title', $validated['Title'])
            ->where('SourceType', $validated['SourceType'])
            ->exists();

            if($duplicate){
                return back()->with('error', 'There is an existing record same as this');
            }

        try{
            DB::beginTransaction();

            $obligations = LegalObligation::create([
                'Title'=> $validated['Title'],
                'SourceType'=> $validated['SourceType'],
                'DueDate'=> $validated['DueDate'],
                'Status'=> $validated['Status'] ?? 'Pending',
                'Description'=> $validated['Description'],
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn(new LegalObligation())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Obligation successfully created');

            DB::commit();

            return redirect()->route('legal.obligations.index')->with('success', 'Obligation created successfully.');
        }catch(\Throwable $th){
            DB::rollBack();

            activity()
                ->performedOn(new LegalObligation())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Obligation successfully created');

            Log::error('Error creating obligation: ' . $th->getMessage());
            return back()->with('error', 'Error creating Obligation: ' . $th->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::LegalObligationUpdate, LegalObligation::class);

        $obligation = LegalObligation::findOrFail($id);
        return view('legal.obligations.edit', compact('obligation'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::LegalObligationUpdate, LegalObligation::class);

        $obligation = LegalObligation::findOrFail($id);

        $data = $request->validate([
            'Title' => 'required|string|max:255',
            'SourceType' => 'required|in:Contract,Case',
            'DueDate' => 'required|date',
            'Status' => 'required|string',
            'Description' => 'required|string',
        ]);

        try{
            DB::beginTransaction();

            $data['ModifiedBy'] = Auth::id();
            $data['ModifiedOn'] = now();

            $obligation->update($data);


            activity()
                ->performedOn(new LegalObligation())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Obligation successfully updated');

            DB::commit();

            return redirect()->route('legal.obligations.index')->with('success', 'Obligation updated successfully.');
        }catch(\Throwable $th){
            DB::rollBack();

            activity()
                ->performedOn(new LegalObligation())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'updated'])
                ->log('Obligation successfully updated');

            Log::error('Error updating obligation: ' . $th->getMessage());
            return back()->with('error', 'Error updating Obligation: ' . $th->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::LegalObligationView, LegalObligation::class);

        $obligation = LegalObligation::findOrFail($id);
        $users = User::select('Name', 'Id', 'Email')
            ->get();
        return view('legal.obligations.show', compact('obligation', 'users'));
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::LegalObligationDelete, LegalObligation::class);

        try{
            DB::beginTransaction();

            $obligation = LegalObligation::findOrFail($id);
            $obligation->DeletedBy = Auth::id();
            $obligation->save();
            $obligation->delete();

            activity()
                    ->performedOn(new LegalObligation())
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'delete'])
                    ->log('Obligation successfully deleted');

            DB::commit();

            return back()->with('success','Obligation successfully deleted');

        }catch(\Throwable $th){
            DB::rollBack();

            activity()
                ->performedOn(new LegalObligation())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Obligation successfully deleted');

            Log::error('Error deleting obligation: ' . $th->getMessage());
            return back()->with('error', 'Error deleting Obligation: ' . $th->getMessage());
        }
    }

    public function getObligations($id)
    {
        $obligations = LegalObligation::findOrFail($id);
        return response()->json($obligations);
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
