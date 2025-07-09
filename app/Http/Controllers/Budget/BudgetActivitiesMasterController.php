<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetActivityMaster;
use App\Models\Budget\BudgetLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetActivitiesMasterController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetActivityMaster::class);
        // Authorize the user to view budget activities
        $activities = BudgetActivityMaster::orderBy('Id', 'asc')->paginate(10);
        $lines = BudgetActivityMaster::with('budgetLine')->get();
        return view('budgetandanalytics.settings.activitymaster.index', compact('activities', 'lines'));
    }
    public function create()
    {
        $lines = BudgetLine::all();
        // Logic to show form for creating a new budget activity
        return view('budgetandanalytics.settings.activitymaster.create', compact('lines'));
    }

    public function store(Request $request)
    {
        // Authorize the user to create a budget activity
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetActivityMaster::class);
        $validated = $request->validate([
            'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
            //'ActivityCode' => 'required|string|max:20|unique:t_BudgetActivityMaster',
            'ActivityName' => 'required|string|max:255',
            'Description'  => 'nullable|string',
            
        ]);
        DB::beginTransaction();
        try {
            $activity = BudgetActivityMaster::create([
                'BudgetLineID' => $validated['BudgetLineID'],
                //'ActivityCode' => $validated['ActivityCode'],
                'ActivityName' => $validated['ActivityName'],
                'Description'  => $validated['Description'],
                'IsActive'     => $request->IsActive=='on'?true: false,
                'CreatedBy' =>Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($activity)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created budget activity: ' . $activity->ActivityName);

            return redirect()->route('activitymaster.index')->with('success', 'Budget activity created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create budget activity: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to create budget activity: ' . $e->getMessage()]);
        }
    }

    public function edit($id)
    {

        
        $activity = BudgetActivityMaster::findOrFail($id);
        $lines = BudgetLine::all();
        return view('budgetandanalytics.settings.activitymaster.edit', compact('activity', 'lines'));
    }

    public function update(Request $request, $id){

        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetActivityMaster::class);

        $validated = $request->validate([
            'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
            //'ActivityCode' => 'required|string|max:20|unique:t_BudgetActivityMaster,ActivityCode,' . $id,
            'ActivityName' => 'required|string|max:255',
            'Description'  => 'nullable|string',
            'IsActive'     => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $activity = BudgetActivityMaster::findOrFail($id);
            $activity->update([
                'BudgetLineID' => $validated['BudgetLineID'],
                //'ActivityCode' => $validated['ActivityCode'],
                'ActivityName' => $validated['ActivityName'],
                'Description'  => $validated['Description'],
                'IsActive'     => $validated['IsActive'] ?? true,
                'ModifiedBy' => Auth::id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($activity)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated budget activity: ' . $activity->ActivityName);

            return redirect()->route('activitymaster.index')->with('success', 'Budget activity updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update budget activity: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update budget activity: ' . $e->getMessage()]);
        }   
    }

    public function destroy($id){

        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetActivityMaster::class);
        DB::beginTransaction();
        try {
            $activity = BudgetActivityMaster::findOrFail($id);
            $activity->DeletedBy = Auth::id();
            $activity->save();
            $activity->delete();
            DB::commit();
            activity()
                ->performedOn($activity)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted budget activity: ' . $activity->ActivityName);
            return redirect()->route('activitymaster.index')->with('success', 'Budget activity deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete budget activity: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to delete budget activity: ' . $e->getMessage()]);
        }   
    }
}
