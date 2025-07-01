<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetScenarioPlanning;
use App\Models\Budget\BudgetTopDown;
use App\Models\Budget\BudgetTopDownData;
use App\Models\Core\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetTopDownAllocationController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetworkspace.topdownallocationtool.index');
    }

    public function create()
    {
        $scenarios = BudgetScenarioPlanning::all();
        $periods = BudgetPeriods::all();
        $lines = BudgetLine::all();
        $branches = Branch::all();

        $budgetId = $validated['BudgetID'];
        $branchId = $validated['BranchID'];
        $check = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
            ->where('BranchID', $branchId)
            ->first();

        $budgetName = Budget::find($budgetId)->Name;
        $branchName = Branch::find($branchId)->Name;

        if ($check) { // records exist

            // Check if this budget is being edited by another user
            $isBeingEdited = BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
                ->where('BranchID', $validated['BranchID'])
                ->where('IsBeingEdited', true)
                ->exists();
            if ($isBeingEdited) {
                // Get the user ID of the person currently editing
                $editingUserId = BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
                    ->where('BranchID', $validated['BranchID'])
                    ->where('IsBeingEdited', true)
                    ->value('IsBeingEditedBy');
                // Get the name of the user who is currently editing
                $editingUserName = User::find($editingUserId)->Name ?? 'Unknown User';
                // Redirect back with an error message if not the same user
                if ($editingUserId !== Auth::id()) {
                    return back()->with('error', "This General Ledger is currently being edited by $editingUserName. Please try again later.");
                }
            } else {
                // Set the IsBeingEdited flag to true for the current user
                BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
                    ->where('BranchID', $validated['BranchID'])
                    ->update([
                        'IsBeingEdited' => true,
                        'IsBeingEditedBy' => Auth::id(),
                    ]);
            }

            $glsMaster = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
                ->where('BranchID', $branchId)
                ->get();
            $isExisting = true;
            return view('budgetandanalytics.budgetworkspace.topdown.exist', compact(
                'budgets',
                'branches',
                'glsMaster',
                'isExisting',
                'budgetName',
                'branchName',
                'budgetId',
                'branchId'
            ));
        } else {
            $glsMaster = BudgetGLsAttachments::where('BudgetID', $budgetId)
                ->whereNull('DeletedOn')
                ->select('Id','AccountID', 'Description', 'GLAccountTypeID')
                ->get();
            $isExisting = false;
            return view('budgetandanalytics.budgetworkspace.topdown.create', compact(
                'budgets',
                'branches',
                'glsMaster',
                'isExisting',
                'budgetName',
                'branchName',
                'budgetId',
                'branchId'
            ));
        }
    }

    public function create(Request $request)
    {
        //Check if such data has been created
        $budgetId = $request->get('BudgetID');
        $branchId = $request->get('BranchID');

        $topDownData = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
            ->where('BranchID', $branchId)
            ->first();
        if ($topDownData) {
            //Load existing data
            $glsMaster = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
                ->where('BranchID', $branchId)
                ->get();
        }else{
            //Read data for GL masters with already prepopulated alloc
            $glsMaster=BudgetGLMaster::select(
                'BudgetGLID',
                'AccountID_CBS',
                'Description',
                'GLAccountTypeID_CBS',
                'GLSubAccountTypeID_CBS'
            )->get();
        }

        return view('budgetandanalytics.budgetworkspace.topdown.create', compact('glsMaster'));

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ScenarioID' => 'required|exists:t_BudgetScenarioPlanning,Id',
            'PeriodID' => 'required|exists:t_BudgetPeriods,Id',
            'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
            'TotalTarget' => 'required|numeric|min:0',
            'Allocations' => 'required|array|min:1',
            'Allocations.*.BranchID' => 'required|exists:t_Branches,Id',
            'Allocations.*.AllocationPercentage' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();

        try {
            $scenarioid = $validated['ScenarioID'];
            $periodid = $validated['PeriodID'];
            $buddgetlineid = $validated['BudgetLineID'];
            $totaltarget = $validated['TotalTarget'];

            //store for t_BudgetTopDown
            $topdown = BudgetTopDown::create([
                'ScenarioID' => $scenarioid,
                'PeriodID' => $periodid,
                'BudgetLineID' => $buddgetlineid,
                'TotalTarget' => $totaltarget,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            foreach ($validated['Allocations'] as $allocation) {

                //store for t_BudgetTopDownData
                $topdowndata = BudgetTopDownData::create([
                    'TopDownID' => $topdown->Id,
                    'BranchID' => $allocation['BranchID'],
                    'AllocationPercentage' => $allocation['AllocationPercentage'],
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            }

            DB::commit();

            activity()
                ->performedOn(new BudgetTopDown())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Top-Down Allocations');

            return redirect()->route('topdownallocation.index')
                ->with('success', 'Top-Down Allocations created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error Storing Top-Down Allocation:' . $th->getMessage());

            return back()->withErrors(['Error' => 'Failed to add Top-Down Allocation:' . $th->getMessage()])
                ->withInput();
        }
    }

}
