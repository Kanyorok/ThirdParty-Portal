<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetGLMaster;
use App\Models\Budget\BudgetGLMasterAllocations;
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

    public function display(Request $request){
        $budgets=Budget::all();
        $branches=Branch::all();
        //return $request->all();
        $validated=$request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'BranchID' => 'required|exists:t_Branches,Id',
        ]);
        //Check if such data has been created
        $budgetId=$validated['BudgetID'];
        $branchId=$validated['BranchID'];
        $check = BudgetGLMasterAllocations::where('BudgetID', $budgetId)
            ->where('BranchID', $branchId)
            ->first();
            if($check){ // Data exists
                //Read data for GL master
                $glsMaster=BudgetGLMasterAllocations::where('BudgetID', $budgetId)
                ->where('BranchID', $branchId)->get();
                $isExisting = true;
                $budgetName=Budget::find($budgetId)->Name;
                $branchName=Branch::find($branchId)->Name;

                return view('budgetandanalytics.budgetworkspace.topdown.exist',compact(
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
            else{
                //Read data for GL master
                $budgetName=Budget::find($budgetId)->Name;
                $branchName=Branch::find($branchId)->Name;
                $glsMaster=BudgetGLMaster::select(
                    'BudgetID',
                    'AccountID',
                    'Description',
                    'GLAccountTypeID',
                    'GLSubAccountTypeID',
                )->get();
                $isExisting = false;
                return view('budgetandanalytics.budgetworkspace.topdown.create',compact(
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
        return $request->all();
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
                $glType = $validated['gl_data'][$accountID]['GLAccountTypeID'] ?? 'NA';

                BudgetGLMasterAllocations::create([
                    'BudgetID' => $validated['budgetId'],
                    'BranchID' => $validated['branchId'],
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
            // Optional: log error for debugging
            Log::error('Budget allocation failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while saving budget allocations. Please try again.');
        }
    }

    public function show($id)
    {
        return redirect()->route('topdownallocation.index');
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'budgetId' => 'required|exists:t_Budgets,Id',
                'branchId' => 'required|exists:t_Branches,Id',
                'monthly_allocations' => 'required|array',
            ]);

            DB::beginTransaction(); // Start transaction

            $userId = Auth::user()->Id;
            $now = Carbon::now();

            foreach ($request->monthly_allocations as $accountID => $months) {
                $total = 0;
                $monthData = [];

                for ($i = 1; $i <= 12; $i++) {
                    $amount = isset($months[$i]) ? (float)$months[$i] : 0;
                    $monthData["Month{$i}"] = $amount;
                    $total += $amount;
                }

                BudgetGLMasterAllocations::updateOrCreate(
                    [
                        'BudgetID' => $request->budgetId,
                        'BranchID' => $request->branchId,
                        'AccountID' => $accountID,
                    ],
                    array_merge([
                        'Total' => $total,
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => $now,
                        'CreatedBy' => $userId,
                        'CreatedOn' => $now,
                        'GLAccountTypeID' => 'NA',
                        'Description' => null,
                    ], $monthData)
                );
            }

            DB::commit(); // Commit if all good

            return back()->with('success', 'Budget allocations updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error

            Log::error('Budget Allocation Update Failed: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'An error occurred while updating budget allocations. Please try again.');
        }
    }

}
