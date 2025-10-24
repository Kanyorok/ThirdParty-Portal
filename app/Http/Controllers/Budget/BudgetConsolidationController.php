<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetActivity;
use App\Models\Budget\BudgetDriverRates;
use App\Models\Budget\BudgetGLAccount;
use App\Models\Budget\BudgetGLAccountSubType;
use App\Models\Budget\BudgetGLMaster;
use App\Models\Budget\BudgetGLSubType;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetManualEntry;
use App\Models\Budget\BudgetManualEntryAllocations;
use App\Models\Budget\BudgetProduct;
use App\Models\Budget\BudgetProductType;
use App\Models\Budget\BudgetProjection;
use App\Models\Budget\BudgetProjectionData;
use App\Models\Core\Branch;
use App\Models\Core\CodeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSXGen;
use Barryvdh\DomPDF\Facade\Pdf;

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
                    })->with('budgetLine:Id,LineName,GLAccountSubTypeID','activity:Id,ActivityName')
                        ->select('Id', 'Description', 'BudgetLineID', 'BranchID', 'AllocationType', 'FullAllocation','ActivityID')
                        ->get();

                    //return $budgetActivities;
                    foreach ($budgetActivities as $activity) {
                        //Get the name for the GLAccountSubType
                        //return $activity;
                        //$glAccountSubType = BudgetGLSubType::find($activity->budgetLine->GLAccountSubTypeID)->Description ?? 'N/A';
                        $glAccountSubType = $activity->budgetLine->LineName ?? 'N/A';
                        $budgetLineName = $activity->activity->ActivityName ?? 'N/A';
                        //Fetch allocation values if allocationType is monthly
                        $allocationValues = [];
                        if ($activity->AllocationType === 'monthly') {
                            $allocationValues = $activity->allocations()->select('Month', 'Amount')->get()->pluck('Amount', 'Month')->toArray();
                        } else {
                            //If not monthly, set allocation values to an empty array
                            $allocationValues = [];
                        }

                        //Fetch Actuals
                        //For Just pick for branch
                        $b_id = Branch::find(session('LoginBranchId'))->BranchID ?? 1;
                        $asDate = date('Y-m-d');
                        $budgetLineID = $activity->BudgetLineID;
                        $result = DB::select(
                            'EXEC dbo.p_GetBudgetLineClosingBalance ?, ?, ?, ?',
                            [$budgetLineID, $b_id, $asDate, 'L']
                        );

                        // $result is an array of objects
                        $actual = ($result[0]->ClosingBalance == '.00' ? 0.00 : $result[0]->ClosingBalance) ?? 0.00;

                        $actual = abs($actual);
                        $change = ($actual / $activity->FullAllocation) * 100;


                        //Store this into the data arrays using keys from gl type value
                        $data[$type->Description][$glAccountSubType][] = [
                            'rate' => 0,
                            'budgetLineName' => $budgetLineName,
                            'allocationValues' => $allocationValues,
                            'allocationType' => $activity->AllocationType,
                            'fullAllocation' => $activity->FullAllocation,
                            'actual' => $actual,
                            'change' => $change,
                        ];
                    }
                    ///////////////////////// Fetching data for Entry By Line //////////////////////////////////////////
                    $entriesByLine = BudgetManualEntry::select('Id', 'BudgetID', 'BudgetLineID', 'BranchID', 'Amount', 'Comments')
                        ->where('BudgetID', $budgetId)
                        ->latest()->get();
                    //Get allocation for this lines and place them in data array
                    foreach ($entriesByLine as $entry) {
                        //return $entry;
                        //Have a check to filter based on the GLType
                        $typeCheck = BudgetLine::find($entry->BudgetLineID)->GLAccountTypeID;
                        if ($typeCheck !== $type->Value) continue;
                        $budgetLine = BudgetLine::find($entry->BudgetLineID);
                        if ($budgetLine) {
                            //$glAccountSubType = BudgetGLAccountSubType::find($budgetLine->glSubType)->GLAccountSubTypeName ?? 'Entry By Line';
                            $glAccountSubType= $budgetLine->glSubType->Description;
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

                            //Fetch Actuals
                            //For Just pick for branch
                            //$b_id = Branch::find(session('LoginBranchId'))->BranchID ?? 1;
                            $b_id = '001';// Branch::find(session('LoginBranchId'))->BranchID ?? 1;
                            $asDate = date('Y-m-d');
                            $budgetLineID = $entry->BudgetLineID;
                            $result = DB::select(
                                'EXEC dbo.p_GetBudgetLineClosingBalance ?, ?, ?, ?',
                                [$budgetLineID, $b_id, $asDate, 'L']
                            );

                            // $result is an array of objects
                            $actual = ($result[0]->ClosingBalance == '.00' ? 0.00 : $result[0]->ClosingBalance) ?? 0.00;

                            $actual = abs($actual);
                            $change = ($actual / $fullAllocation) * 100;
                            $data[$type->Description][$glAccountSubType][] = [
                                'rate' => 0,
                                'budgetLineName' => $budgetLineName,
                                'allocationValues' => $allocationValues,
                                'allocationType' => 'monthly',
                                'fullAllocation' => $fullAllocation,
                                'actual' => $actual,
                                'change' => $change,
                            ];
                        }
                    }
                }
                ////////////////////////////////// Get data from Budget Projections //////////////////////////////////////////////
                //Get all projection data
                $projections = BudgetProjection::where('BudgetID', $budgetId)->get();
                //Loop through the projections
                foreach ($projections as $projection) {
                    //Get the product Gltype
                    $product = BudgetProduct::find($projection->ProductID);
                    $productGLAccountID = $product->GLAccountID;
                    $glAccountSubType = BudgetGLSubType::find($projection->budgetLine->GLAccountSubTypeID)->Description ?? 'N/A';
                    $GLType = BudgetGLMaster::where('AccountID', $productGLAccountID)->pluck('GLAccountTypeID')->first() ?? 'N/A';
                    $GLType = CodeDetail::where('CodeID', 'GLAccountType')->where('Value', $GLType)->pluck('Description')->first();

                    //Check allocation
                    $allocationType = $projection->AllocationType;
                    $allocationValues = []; //To help store monthly allocation
                    if ($allocationType === 'monthly') {
                        $values = BudgetProjectionData::where('BudgetProjectionID', $projection->Id)->where('BudgetId', $budgetId)
                            ->select('Month', 'Amount')
                            ->get()
                            ->pluck('Amount', 'Month')
                            ->toArray();
                        if ($values) {
                            $allocationValues = $values;
                        }
                        $fullAllocation = BudgetProjectionData::where('BudgetProjectionID', $projection->Id)->where('BudgetId', $budgetId)
                            ->sum('Amount');
                    } else {
                        $fullAllocation = BudgetProjection::where('Id', $projection->Id)->pluck('FullAllocation')->first() ?? 0;
                    }


                    //Fetch Actuals
                    //For Just pick for branch
                    $b_id = Branch::find(session('LoginBranchId'))->BranchID ?? 1;
                    $asDate = date('Y-m-d');
                    $budgetLineID = $projection->BudgetLineID;
                    $result = DB::select(
                        'EXEC dbo.p_GetBudgetLineClosingBalance ?, ?, ?, ?',
                        [$budgetLineID, $b_id, $asDate, 'L']
                    );

                    // $result is an array of objects
                    $actual = ($result[0]->ClosingBalance == '.00' ? 0.00 : $result[0]->ClosingBalance) ?? 0.00;

                    $actual = abs($actual);
                    $change = ($actual / $fullAllocation) * 100;


                    //Store data for the GL first in the data array and in the respective GL section
                    $data[$GLType][$glAccountSubType][] = [
                        'rate' => 0,
                        'budgetLineName' => $product->Description,
                        'allocationValues' => $allocationValues,
                        'allocationType' => $allocationType,
                        'fullAllocation' => $fullAllocation,
                        'actual' => $actual,
                        'change' => $change,
                    ];
                    //Now get the Budgetline for that product
                    $budgetLineID = $projection->BudgetLineID;
                    $budgetlineData = BudgetLine::find($budgetLineID);
                    $BudgetLineSubGLID = $budgetlineData->GLAccountSubTypeID;
                    $budgetLineGLData = BudgetGLMaster::find($BudgetLineSubGLID);
                    $budgetLineGLType = $budgetLineGLData->GLAccountTypeValue;
                    $glAccountSubType = BudgetGLSubType::find($projection->budgetLine->GLAccountSubTypeID)->Description ?? 'N/A';
                    if ($budgetLineGLType == 'A') {
                        $budgetLineGLType = 'ASSET';
                    } elseif ($budgetLineGLType == 'L') {
                        $budgetLineGLType = 'LIABILITY';
                    } elseif ($budgetLineGLType == 'E') {
                        $budgetLineGLType = 'EXPENSE';
                    } else {
                        $budgetLineGLType = 'INCOME';
                    }
                    //$budgetLineGLType=CodeDetail::where('CodeID', 'GLAccountType')->where('Value', $budgetLineGLType)->pluck('Description')->first();
                    $budgetLineGLName = $budgetLineGLData->Description;
                    $budgetLineName = $budgetlineData->LineName;
                    //Get the Product rate value
                    $p_code = $product->ProductTypeID;
                    $productTypeID = BudgetProductType::where('ProductCode', $p_code)->first();
                    $rateValue = BudgetDriverRates::where('ProductTypeID', $product->Id)->pluck('RateValue')->first();
                    //Check for allocations and compute the allocation to be inserted into the data array
                    $allocationValues = [];
                    $values = BudgetProjectionData::where('BudgetProjectionID', $projection->Id)->where('BudgetId', $budgetId)
                        ->select('Month', 'Amount')
                        ->get()
                        ->pluck('Amount', 'Month')
                        ->toArray();
                    //Multiply with the rate value
                    if ($values) {
                        foreach ($values as $month => $amount) {
                            $allocationValues[$month] = ($amount * $rateValue) / 100;
                        }
                    }
                    //Insert the data set 2 for the budgetline into the data array
                    $data[$budgetLineGLType][$glAccountSubType][] = [
                        'rate' => $rateValue,
                        'budgetLineName' => $product->Description, //$budgetLineName,
                        'allocationValues' => $allocationValues,
                        'allocationType' => $allocationType,
                        'fullAllocation' => ($fullAllocation * $rateValue) / 100,
                        'actual' => $actual,
                        'change' => $change,
                    ];
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

    public function exportExcel(Request $request)
    {
        $rows = $this->buildExportRows($request);
        $rawName = (string)$request->input('budgetName', 'Budget');
        $safeName = trim(preg_replace('/[^A-Za-z0-9\- _\.]+/', '', $rawName));
        $safeName = $safeName !== '' ? $safeName : 'Budget';
        $filename = $safeName . '-budget-consolidation-' . now()->format('Ymd_His') . '.xlsx';
        $xlsx = SimpleXLSXGen::fromArray($rows)->download($filename);
        // SimpleXLSXGen::download() echoes and exits; as a fallback return a standard response
        return response()->noContent();
    }

    public function exportPdf(Request $request)
    {
        $rows = $this->buildExportRows($request);
        $pdf = Pdf::loadView('exports.budget_consolidation_pdf', ['rows' => $rows])
            ->setPaper('A4', 'landscape');
        $rawName = (string)$request->input('budgetName', 'Budget');
        $safeName = trim(preg_replace('/[^A-Za-z0-9\- _\.]+/', '', $rawName));
        $safeName = $safeName !== '' ? $safeName : 'Budget';
        $filename = $safeName . '-budget-consolidation-' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    private function buildExportRows(Request $request): array
    {
        $headers = ['Category', 'Sub Type', 'Budget Line', 'Rate %', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Total', 'Actuals', '% Change'];
        $rows = [$headers];
        $raw = $request->input('rows', []);
        $payload = is_array($raw) ? $raw : (json_decode((string)$raw, true) ?: []);
        foreach ($payload as $r) {
            $rows[] = [
                $r['category'] ?? '',
                $r['subType'] ?? '',
                $r['budgetLineName'] ?? '',
                $r['rate'] ?? '',
                ($r['months'][1] ?? ''), ($r['months'][2] ?? ''), ($r['months'][3] ?? ''), ($r['months'][4] ?? ''), ($r['months'][5] ?? ''), ($r['months'][6] ?? ''),
                ($r['months'][7] ?? ''), ($r['months'][8] ?? ''), ($r['months'][9] ?? ''), ($r['months'][10] ?? ''), ($r['months'][11] ?? ''), ($r['months'][12] ?? ''),
                $r['total'] ?? '',
                $r['actuals'] ?? '',
                $r['change'] ?? '',
            ];
        }
        return $rows;
    }
}
