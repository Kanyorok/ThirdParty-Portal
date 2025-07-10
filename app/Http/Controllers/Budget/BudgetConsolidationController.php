<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetGLAccountSubType;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetManualEntry;
use App\Models\Budget\BudgetManualEntryAllocations;
use App\Models\Core\CodeDetail;
use Illuminate\Http\Request;

class BudgetConsolidationController extends Controller
{
    //
    public function index()
    {
        // /return 1;
        //Define data array that will store the data for all types entry
        $data = [];
        $isSet = false;
        $budgetId = null;
        $budgetName = '';
        $period = null;
        //check if request comes with a budget id
        if (request()->has('BudgetLineID')) {

            $budgetId = request()->get('BudgetLineID');
            $budget = Budget::find($budgetId);
            if (!$budget) {
                return redirect()->back()->with('error', 'Budget not found');
            } else {
                //Fetching the GLACCountTypes From Core details
                $isSet = true;
                $budgetName = $budget->Name ?? '';
                $period = $budget->From . ' - ' . $budget->To;
                $glAccountTypes = CodeDetail::where('CodeID', 'GLAccountType')->select('Value', 'Description')->get();
                foreach ($glAccountTypes as $type) {

                    /////////////////////////// Fetching the Activities assoc with the Budget //////////////////////////////////////////
                    $budgetActivities = $budget->activities()->whereHas('budgetLine', function ($query) use ($type) {
                        $query->where('GLAccountTypeID', $type->Value);
                    })->with('budgetLine:Id,LineName,GLAccountSubTypeID')
                        ->select('Id', 'Description', 'BudgetLineID', 'BranchID', 'AllocationType', 'FullAllocation')
                        ->get();

                    //return $budgetActivities;
                    foreach ($budgetActivities as $activity) {
                        //Get the name for the GLAccountSubType
                        $glAccountSubType = BudgetGLAccountSubType::find($activity->budgetLine->GLAccountSubTypeID)->GLAccountSubTypeName ?? 'N/A';
                        $budgetLineName = $activity->budgetLine->LineName ?? 'N/A';
                        //Fetch allocation values if allocationType is monthly
                        $allocationValues = [];
                        if ($activity->AllocationType === 'monthly') {
                            $allocationValues = $activity->allocations()->select('Month', 'Amount')->get()->pluck('Amount', 'Month')->toArray();
                        } else {
                            //If not monthly, set allocation values to an empty array
                            $allocationValues = [];
                        }

                        //Store this into the data arrays using keys from gl type value
                        $data[$type->Description][$glAccountSubType][] = [
                            'budgetLineName' => $budgetLineName,
                            'allocationValues' => $allocationValues,
                            'allocationType' => $activity->AllocationType,
                            'fullAllocation' => $activity->FullAllocation,
                        ];
                    }
                    ///////////////////////// Fetching data for Entry By Line //////////////////////////////////////////
                    $entriesByLine = BudgetManualEntry::select('Id', 'BudgetID', 'BudgetLineID', 'BranchID', 'Amount', 'Comments')->where('BudgetID', $budgetId)->latest()->get();
                    //Get allocation for this lines and place them in data array
                    foreach ($entriesByLine as $entry) {
                        $budgetLine = BudgetLine::find($entry->BudgetLineID);
                        if ($budgetLine) {
                            $glAccountSubType = BudgetGLAccountSubType::find($budgetLine->GLAccountSubTypeID)->GLAccountSubTypeName ?? 'N/A';
                            $budgetLineName = $budgetLine->LineName ?? 'N/A';
                            //fetch allocations
                            $allocationValues = [];
                            $values = BudgetManualEntryAllocations::where('EntryID', $entry->Id)->where('BudgetId', $budgetId)
                                ->select('Month', 'Allocation')
                                ->get()
                                ->pluck('Allocation', 'Month')
                                ->toArray();
                            if ($values) {
                                $allocationValues = $values;
                            }
                            $fullAllocation = BudgetManualEntry::where('Id', $entry->Id)->pluck('Amount')->first() ?? 0;
                            //return $glAccountSubType;
                            $data[$type->Description][$glAccountSubType][] = [
                                'budgetLineName' => $budgetLineName,
                                'allocationValues' => $allocationValues,
                                'allocationType' => 'monthly',
                                'fullAllocation' => $fullAllocation,
                            ];
                        }
                    }
                }
            }
        }

        $budgets = Budget::select('Id', 'Name')->get();
        return view('budgetandanalytics.budgetworkspace.budgetconsolidation.index',
            compact('budgets', 'data', 'isSet', 'budgetId', 'budgetName', 'period')
        );
    }

    public function create()
    {
        return view('budgetandanalytics.budgetworkspace.budgetconsolidation.create');
    }
}
