<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetGLMaster;
use App\Models\Budget\BudgetGLMasterAllocations;
use App\Models\Budget\BudgetGLsAttachments;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetScenarioPlanning;
use App\Models\Budget\BudgetTopDown;
use App\Models\Budget\BudgetTopDownData;
use App\Models\Core\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetTopDownAllocationController extends Controller
{
    //
    public function index()
    {
        $budgets=Budget::all();
        $branches=Branch::all();

        //Read data for GL master
        // $glsMaster=BudgetGLMaster::select(
        //     'BudgetGLID',
        //     'AccountID',
        //     'Description',
        //     'GLAccountTypeID',
        //     'GLSubAccountTypeID'
        // )->get();

        return view('budgetandanalytics.budgetworkspace.topdown.index',compact(
            'budgets',
            'branches',
            //'glsMaster',
        ));
    }

    public function display(Request $request)
    {
        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'BranchID' => 'required|exists:t_Branches,Id',
        ]);

        // //Check if its being edited
        // $isBeingEdited = BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
        //     ->where('BranchID', $validated['BranchID'])
        //     ->where('IsBeingEdited', true)
        //     ->exists();
        // if ($isBeingEdited) {// 
        //     //get user name who is editing
        //     $editingUser = BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
        //         ->where('BranchID', $validated['BranchID'])
        //         ->where('IsBeingEdited', true)
        //         ->value('IsBeingEditedBy');
        //     // Get the name of the user who is currently editing
        //     $editingUserName = User::find($editingUser)->name ?? 'Unknown User';
        //     // Redirect back with an error message
        //     return redirect()->back()->with('error', "This budget allocation is currently being edited by $editingUserName. Please try again later.");
        // }else//Set the edit true
        // {
        //     // Set the IsBeingEdited flag to true for the current user
        //     BudgetGLMasterAllocations::where('BudgetID', $validated['BudgetID'])
        //         ->where('BranchID', $validated['BranchID'])
        //         ->update([
        //             'IsBeingEdited' => true,
        //             'IsBeingEditedBy' => Auth::id(),
        //             'ModifiedOn' => Carbon::now(),
        //         ]);
        // }

        $budgets = Budget::all();
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
                // Redirect back with an error message
                return back()->with('error', "This General Ledger is currently being edited by $editingUserName. Please try again later.");
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
            'budgetId' => 'required|exists:t_Budgets,Id',
            'branchId' => 'required|exists:t_Branches,Id',
            //'format' => 'required|in:m,q',
            'monthly_allocations' => 'required|array',
            'gl_data' => 'required|array',
        ]);

        $userId = Auth::user()->Id;
        $now = Carbon::now();

        try {
            DB::beginTransaction();

            foreach ($validated['monthly_allocations'] as $accountID => $months) {
                $total = 0;
                $monthData = [];

                for ($i = 1; $i <= 12; $i++) {
                    $amount = isset($months[$i]) ? (float)$months[$i] : 0;
                    $monthData["Month{$i}"] = $amount;
                    $total += $amount;
                }

                $description = $validated['gl_data'][$accountID]['Description'] ?? null;
                $glAttachmentId = $validated['gl_data'][$accountID]['AttachID'] ?? null;
                $glType = $validated['gl_data'][$accountID]['GLAccountTypeID'] ?? 'NA';

                BudgetGLMasterAllocations::create([
                    'BudgetID' => $validated['budgetId'],
                    'BranchID' => $validated['branchId'],
                    'GLAttachmentID' => $glAttachmentId, // Assuming this is not used in the new structure
                    'AccountID' => $accountID,
                    'Description' => $description,
                    'GLAccountTypeID' => $glType,
                    'Total' => $total,
                    'CreatedBy' => $userId,
                    'CreatedOn' => $now,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                    ...$monthData,
                ]);
            }
            DB::commit();

            return redirect()->back()->with('success', 'GL Budget Allocations saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            //return $e->getMessage();
            // Optional: log error for debugging
            Log::error('Budget allocation failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while saving budget allocations. Please try again.');
        }
    }

    public function show($id)
    {
        return redirect()->route('topdownallocation.index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'budgetId' => 'required|exists:t_Budgets,Id',
            'branchId' => 'required|exists:t_Branches,Id',
            'monthly_allocations' => 'required|array',
            'monthly_allocations.*' => 'array',
            'monthly_allocations.*.*' => 'nullable|numeric|min:0|max:234432554',
        ]);

        DB::beginTransaction();
        try {
            $userId = Auth::id();
            $now = Carbon::now();

            foreach ($validated['monthly_allocations'] as $accountID => $months) {
                $total = 0;
                $monthData = [];

                for ($i = 1; $i <= 12; $i++) {
                    $amount = isset($months[$i]) ? (float)$months[$i] : 0;
                    $monthData["Month{$i}"] = $amount;
                    $total += $amount;
                }

                BudgetGLMasterAllocations::updateOrCreate(
                    [
                        'BudgetID' => $validated['budgetId'],
                        'BranchID' => $validated['branchId'],
                        'AccountID' => $accountID,
                    ],
                    array_merge([
                        'Description' => BudgetGLsAttachments::where('BudgetID', $validated['budgetId'])
                            ->where('AccountID', $accountID)
                            ->whereNull('DeletedOn')
                            ->value('Description'),
                        'GLAccountTypeID' => BudgetGLsAttachments::where('BudgetID', $validated['budgetId'])
                            ->where('AccountID', $accountID)
                            ->whereNull('DeletedOn')
                            ->value('GLAccountTypeID'),
                        'Total' => $total,
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => $now,
                        'CreatedBy' => $userId,
                        'CreatedOn' => $now,
                    ], $monthData)
                );
            }

            //Remove the IsBeingEdited flag
            BudgetGLMasterAllocations::where('BudgetID', $validated['budgetId'])
                ->where('BranchID', $validated['branchId'])
                ->update([
                    'IsBeingEdited' => false,
                    'IsBeingEditedBy' => null,
                    'ModifiedOn' => Carbon::now(),
                ]);

            DB::commit();


            activity()
                ->causedBy(Auth::user())
                ->event('update')
                ->withProperties(['action' => 'update_allocations', 'BudgetID' => $validated['budgetId'], 'BranchID' => $validated['branchId']])
                ->log('Updated budget allocations');

            return redirect()->route('topdownallocation.index')
                ->with('success', 'Budget allocations updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Budget Allocation Update Failed: ' . $th->getMessage());
            return back()->withInput()->withErrors(['error' => 'An error occurred while updating budget allocations. Please try again.']);
        }
    }

}
