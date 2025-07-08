<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetActivity;
use App\Models\Budget\BudgetActivityMaster;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetMonthlyAllocation;
use App\Models\Core\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 

class BudgetActivitiesController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetActivity::class);
        $activities = BudgetActivity::with([
            'budget:Id,Name,From,To',
            //'allocations:Id,BudgetActivityID,Month,Amount', 
            //'branch:Id,Name', 
            //'budgetLine:Id,LineName',
        ])->get();

        // Group activities by BudgetID
        $grouped = $activities->groupBy('BudgetID');

        return view('budgetandanalytics.budgetactivities.index', [
            'groupedActivities' => $grouped
        ]);
    }

    public function create()
    {
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetActivity::class);

        $budgetLines=BudgetLine::select('Id','LineName')->get();
        $branches=Branch::select('Id','Name')->get();
        $budgets=Budget::all();

        return view('budgetandanalytics.budgetactivities.create',compact(
            'budgetLines',
            'branches',
            'budgets'
        ));
    }


    public function store(Request $request){
        //Check for permission
        $this->authorize(PermissionEnum::BudgetSetupCreate,BudgetActivity::class);
        // Validate request
        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
            'ActivityID' => 'required|exists:t_BudgetActivityMaster,Id',
            'Description' => 'required|string',
            //'BranchID' => 'required|exists:t_Branches,Id',
            'AllocationType' => 'required|in:full,monthly',
            'FullAllocation' => 'nullable|numeric|min:0',
            'monthly_allocations' => 'nullable|array',
            'monthly_allocations.*' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $now = now();

            // Calculate full allocation from monthly if applicable
            $fullAllocation = 0;
            if ($validated['AllocationType'] === 'monthly' && !empty($validated['monthly_allocations'])) {
                $fullAllocation = collect($validated['monthly_allocations'])->sum(function ($value) {
                    return is_numeric($value) ? floatval($value) : 0;
                });
            } elseif ($validated['AllocationType'] === 'full') {
                $fullAllocation = $validated['FullAllocation'] ?? 0;
            }
            // Create Budget Activity
            $activity = BudgetActivity::create([
                'BudgetLineID' => $validated['BudgetLineID'],
                'BudgetID' => $validated['BudgetID'],
                'ActivityID' => $validated['ActivityID'],
                'Description' => $validated['Description'],
                'BranchID' => 1,//$validated['BranchID'], To be fixed when Login branch is implemented
                'AllocationType' => $validated['AllocationType'],
                'FullAllocation' => $fullAllocation,
                'CreatedBy' => $userId,
                'ModifiedBy' => $userId,
                'CreatedOn' => $now,
                'ModifiedOn' => $now,
            ]);

            // Handle Monthly Allocations (store all months, even 0.00)
            if ($validated['AllocationType'] === 'monthly' && !empty($validated['monthly_allocations'])) {
                foreach ($validated['monthly_allocations'] as $month => $amount) {
                    BudgetMonthlyAllocation::create([
                        'BudgetActivityID' => $activity->Id,
                        'Month' => (int) $month,
                        'Amount' => floatval($amount),
                        'CreatedBy' => $userId,
                        'ModifiedBy' => $userId,
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                    ]);
                }
            }
            // Log activity
            activity()
                ->performedOn($activity)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created a budget activity');
            DB::commit();
            return redirect()->route('budgetactivities.index')->with('success', 'Budget Activity created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to store budget activity.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while creating the budget activity. Please try again.');
        }
    }


    public function fetchActivities(Request $request)
    {
        $budgetLineId = $request->query('budget_line_id');
        if (!$budgetLineId)  return response()->json(['error' => 'Missing budget_line_id'], 400);
        $activities = BudgetActivityMaster::where('BudgetLineID', $budgetLineId)
            ->select('Id', 'ActivityName')
            ->get();
        return response()->json($activities);
    }

    public function show($budgetId)
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetActivity::class);

        $budget = Budget::findOrFail($budgetId);
        $activities = BudgetActivity::with([
        'activity:Id,ActivityName', 
        'budgetLine', 
        'branch', 
        'allocations'])
            ->where('BudgetID', $budgetId)
            ->get();
        return view('budgetandanalytics.budgetactivities.show', compact('budget', 'activities'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetActivity::class);
        // Fetch the activity with its allocations
        $activity = BudgetActivity::with(['allocations'])->findOrFail($id);
        $budgetLines = BudgetLine::select('Id','LineName')->get();
        $branches = Branch::select('Id','Name')->get();
        $budgets = Budget::all();
        $budgetName=Budget::find($activity->BudgetID)->Name;
        $monthlyAllocations = $activity->allocations->keyBy('Month');
        return view('budgetandanalytics.budgetactivities.edit', compact(
            'activity',
            'budgetLines',
            'branches',
            'budgets',
            'monthlyAllocations',
            'budgetName'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetActivity::class);
        $validated = $request->validate([
            // 'BudgetID' => 'required|exists:t_Budgets,Id',
            'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
            'ActivityID' => 'required|exists:t_BudgetActivityMaster,Id',
            'Description' => 'required|string',
            //'BranchID' => 'required|exists:t_Branches,Id',
            'AllocationType' => 'required|in:full,monthly',
            'FullAllocation' => 'nullable|numeric|min:0',
            'monthly_allocations' => 'nullable|array',
            'monthly_allocations.*' => 'nullable|numeric|min:0',
        ]);
        try {
            DB::beginTransaction();
            $userId = Auth::id();
            $now = now();
            $activity = BudgetActivity::findOrFail($id);
            $budgetId=$activity->BudgetID;
            // Calculate full allocation from monthly if applicable
            $fullAllocation = 0;
            if ($validated['AllocationType'] === 'monthly' && !empty($validated['monthly_allocations'])) {
                $fullAllocation = collect($validated['monthly_allocations'])->sum(function ($value) {
                    return is_numeric($value) ? floatval($value) : 0;
                });
            } elseif ($validated['AllocationType'] === 'full') {
                $fullAllocation = $validated['FullAllocation'] ?? 0;
            }
            $activity->update([
                'BudgetLineID' => $validated['BudgetLineID'],
                // 'BudgetID' => $validated['BudgetID'],
                'ActivityID' => $validated['ActivityID'],
                'Description' => $validated['Description'],
                'BranchID' => 1,//$validated['BranchID'],
                'AllocationType' => $validated['AllocationType'],
                'FullAllocation' => $fullAllocation,
                'ModifiedBy' => $userId,
                'ModifiedOn' => $now,
            ]);
            // Handle Monthly Allocations
            $activity->allocations()->delete();
            if ($validated['AllocationType'] === 'monthly' && !empty($validated['monthly_allocations'])) {
                foreach ($validated['monthly_allocations'] as $month => $amount) {
                    BudgetMonthlyAllocation::create([
                        'BudgetActivityID' => $activity->Id,
                        'Month' => (int) $month,
                        'Amount' => floatval($amount),
                        'CreatedBy' => $userId,
                        'ModifiedBy' => $userId,
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                    ]);
                }
            }
           
            activity()
                ->performedOn($activity)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated a budget activity');
            DB::commit();
            return redirect()->route('budgetactivities.show',$budgetId)->with('success', 'Budget Activity updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to update budget activity.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while updating the budget activity. Please try again.');
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetActivity::class);
        try {
            DB::beginTransaction();
            $activity = BudgetActivity::findOrFail($id);
            $activityId=$activity->BudgetActivityID;
            // $activity->allocations()->delete();
            // $allocations = BudgetMonthlyAllocation::where('BudgetActivityID', $activityId)->get();

            // foreach($allocations as $allocation ){
            //     $allocations->DeletedBy = Auth::Id();
            //     $allocation->save();
            //     $allocation->delete();
            // }
            $activity->DeletedBy = Auth ::Id();
            $activity->save();
            $activity->delete();
            activity()
                ->performedOn($activity)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted a budget activity');
            DB::commit();
            return back()->with('success', 'Budget Activity deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to delete budget activity.', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while deleting the budget activity. Please try again.');
        }
    }
}
