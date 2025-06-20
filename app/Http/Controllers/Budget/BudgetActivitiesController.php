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
        $activities = BudgetActivity::with([
            'allocations:Id,BudgetActivityID,Month,Amount',
            'branch:Id,Name',
            'budgetLine:Id,LineName',
            'activity:Id,ActivityName'
        ])->get();
        return view('budgetandanalytics.budgetactivities.index', compact('activities'));
    }

    public function create()
    {
        $budgetLines = BudgetLine::select('Id', 'LineName')->get();
        $branches = Branch::select('Id', 'Name')->get();
        $budgets = Budget::all();

        return view('budgetandanalytics.budgetactivities.create', compact(
            'budgetLines',
            'branches',
            'budgets'
        ));
    }


    public function store(Request $request)
    {
        //Check for permission
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetActivity::class);
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
                        'Month' => (int)$month,
                        'Amount' => floatval($amount),
                        'CreatedBy' => $userId,
                        'ModifiedBy' => $userId,
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                    ]);
                }
            }

            DB::commit();

            // Log activity
            activity()
                ->performedOn($activity)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created a budget activity');

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
        if (!$budgetLineId) return response()->json(['error' => 'Missing budget_line_id'], 400);
        $activities = BudgetActivityMaster::where('BudgetLineID', $budgetLineId)
            ->select('Id', 'ActivityName')
            ->get();
        return response()->json($activities);
    }

}
