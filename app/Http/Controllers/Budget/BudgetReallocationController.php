<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\ReallocationRequest;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetGLMaster;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetActivity;
use App\Models\Budget\BudgetActivityMaster;
use App\Models\Budget\BudgetLineLedgerLimit;
use App\Models\Budget\BudgetMonthlyAllocation;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetReallocation;
use App\Models\Budget\BudgetManualEntry;
use App\Models\Core\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BudgetReallocationController extends Controller
{
    public function index()
    {
        $reallocations = BudgetReallocation::orderBy('CreatedOn', 'desc')->get();
        return view('budgetandanalytics.reallocation.index', compact('reallocations'));
    }

    public function create()
    {
        $budgets     = Budget::where('Status', 'approved')->where('IsLimitSet',true)->get();
        $branches    = \App\Models\Core\Branch::all();
        $departments = \App\Models\HRM\Department::all();
        $lines       = BudgetLine::all();

        $loginBranchId = session('LoginBranchId');
        $isHeadOffice=false;
        $check=Branch::where('Id',$loginBranchId)->where('BranchID','000')->exists();
        if($check){
            $isHeadOffice=true;
        }

        return view('budgetandanalytics.reallocation.create',
            compact('budgets', 'branches', 'departments', 'lines','isHeadOffice'));
    }

    public function allocate(ReallocationRequest $request)
    {
        $validated = $request->validated();
        $budgetLines=array();
        $budgetId=$validated['BudgetID'];
        $branchId='';
        //Below two to be used when its across departments
        $fromLines=[];
        $toLines=[];
        $isAccrossDepertments=false;

        // Example branching logic
        if ($validated['ReallocationType'] === 'Branch') {
            // Collect all line IDs from activities
            $branchId=$validated['BranchID'];
            $budgetLines=$this->getBudgetLinesData($budgetId, $branchId,0);
        }

        if ($validated['ReallocationType'] === 'Department') {
            // Collect all line IDs from activities
            $banchIDHQ=Branch::where('BranchID','000')->pluck('Id')->first();
            $branchId=$banchIDHQ;
            $budgetLines=$this->getBudgetLinesData($budgetId, $branchId,$validated['DepartmentID']);
        }

        if ($validated['ReallocationType'] === 'Cross-Department') {
            $budgetLines=[];
            $banchIDHQ=Branch::where('BranchID','000')->pluck('Id')->first();
            $branchId=$banchIDHQ;
            $fromLines=$this->getBudgetLinesData($budgetId, $branchId,$validated['DepartmentID']);
            $toLines=$this->getBudgetLinesData($budgetId, $branchId,$validated['ToDepartmentID']);
            $isAccrossDepertments=true;
        }

        $loginBranchId = session('LoginBranchId');
        $isHeadOffice=false;
        $check=Branch::where('Id',$loginBranchId)->where('BranchID','000')->exists();
        if($check){
            $isHeadOffice=true;
        }

        $budget=Budget::findOrFail($budgetId);
        $msg='Re-allocate Budget: '.$budget->Name;

        return view('budgetandanalytics.reallocation.allocate',
            compact('isHeadOffice','budgetLines','branchId','budget','budgetId','msg','isAccrossDepertments','fromLines','toLines'));
    }

    protected function getBudgetLinesData($budgetID, $branchID, $departmentID)
    {
        // Get line IDs from BudgetActivity
        $lineIdsFromActivities = BudgetActivity::where('BudgetID', $budgetID)
            ->where('BranchID', $branchID)
            ->whereNotNull('BudgetLineID')
            ->pluck('BudgetLineID')
            ->toArray();

        // Get line IDs from BudgetManualEntry
        $lineIdsFromManual = BudgetManualEntry::where('BudgetID', $budgetID)
            ->where('BranchID', $branchID)
            ->pluck('BudgetLineID')
            ->toArray();

        // Merge and make IDs unique
        $allLineIds = array_unique(array_merge($lineIdsFromActivities, $lineIdsFromManual));

        // Fetch all budget lines
        if($departmentID!==0){
            return BudgetLine::whereIn('Id', $allLineIds)
                ->where('DepartmentID', $departmentID)
                ->orderBy('LineName')
                ->get(['Id', 'LineName'])
                ->map(function ($line) {
                    return [
                        'Id'       => $line->Id,
                        'LineName' => $line->LineName,
                    ];
                })
                ->toArray();
        }else{
            return BudgetLine::whereIn('Id', $allLineIds)
                ->orderBy('LineName')
                ->get(['Id', 'LineName'])
                ->map(function ($line) {
                    return [
                        'Id'       => $line->Id,
                        'LineName' => $line->LineName,
                    ];
                })
                ->toArray();
        }
    }



    public function store(Request $request)
    {
        // 1) Validate input
        return$validated = $request->validate([
            'BudgetID'         => ['required','integer','exists:t_Budgets,Id'],
            'FromBudgetLineID' => ['required','integer','exists:t_BudgetLines,Id'],
            'ToBudgetLineID'   => ['required','integer','different:FromBudgetLineID','exists:t_BudgetLines,Id'],
            'Amount'           => ['required','numeric','min:0.01'],
            'Justification'    => ['required','string'],
            'ReallocationType' => ['required','string'], // Branch, Dept, Cross-Dept
            'BranchID'         => ['required','integer','exists:t_Branches,Id'], // needed for limits
            'DepartmentID'     => ['nullable','integer','exists:t_Departments,Id'],

            // Monthly arrays (only future months are sent since past inputs are disabled)
            'FromAllocations'  => ['required','array'],
            'ToAllocations'    => ['required','array'],
            'FromAllocations.*'=> ['nullable','numeric','min:0'],
            'ToAllocations.*'  => ['nullable','numeric','min:0'],
        ]);

        $budget    = Budget::findOrFail($validated['BudgetID']);
        $branchDbId= (int) $validated['BranchID'];
        $fromLineId= (int) $validated['FromBudgetLineID'];
        $toLineId  = (int) $validated['ToBudgetLineID'];
        $amount    = (float) $validated['Amount'];

        // 2) Build the fiscal months list (index 1..12 aligns with your inputs)
        $months = [];
        $cursor = Carbon::parse($budget->From)->startOfMonth();
        $end    = Carbon::parse($budget->To)->startOfMonth();
        $i = 1;
        while ($cursor <= $end && $i <= 12) {
            // store a Carbon instance per index (1-based)
            $months[$i] = $cursor->copy();
            $cursor->addMonth();
            $i++;
        }

        // 3) Helper to compute balance (Allocated - Usage)
        $activeLimitsOnly = true; // set false to include pending as well
        $balanceFor = function (int $budgetLineId) use ($validated, $branchDbId, $activeLimitsOnly) {
            // Allocated (sum of active limits)
            $allocated = BudgetLineLedgerLimit::where('BudgetID', $validated['BudgetID'])
                ->where('BudgetLineID', $budgetLineId)
                ->where('BranchID', $validated['BranchID'])
                ->when($activeLimitsOnly, fn($q) => $q->where('IsActive', 1))
                ->sum('LimitAmount');

            // Usage via SP (CBS branch id)
            $cbsBranchId = Branch::findOrFail($branchDbId)->BranchID;
            $asDate = Carbon::today()->format('d M Y'); // or align to budget end, if required
            $res = DB::select('EXEC dbo.p_GetBudgetLineClosingBalance ?, ?, ?, ?', [
                $budgetLineId, $cbsBranchId, $asDate, 'L'
            ]);
            $usage = (isset($res[0]->ClosingBalance) && $res[0]->ClosingBalance !== '.00')
                ? (float) $res[0]->ClosingBalance
                : 0.0;

            return (float) $allocated - (float) $usage;
        };

        $fromBalance = $balanceFor($fromLineId);
        $toBalance   = $balanceFor($toLineId);

        // 4) Server-side business rules
        if ($amount <= 0) {
            throw ValidationException::withMessages(['Amount' => 'Enter a positive amount to reallocate.']);
        }
        if ($amount > $fromBalance) {
            throw ValidationException::withMessages([
                'Amount' => "Insufficient balance on From line. Needed ".number_format($amount,2)." available ".number_format($fromBalance,2)."."
            ]);
        }

        $fromTarget = max(0, $fromBalance - $amount); // AFTER reallocation
        $toTarget   = $toBalance + $amount;           // AFTER reallocation

        $sumArray = function(array $arr): float {
            $sum = 0.0;
            foreach ($arr as $v) {
                if ($v === null || $v === '') continue;
                $sum += (float) $v;
            }
            return round($sum, 2);
        };

        $fromMonthly = $request->input('FromAllocations', []);
        $toMonthly   = $request->input('ToAllocations', []);

        $fromTotal   = $sumArray($fromMonthly);
        $toTotal     = $sumArray($toMonthly);

        // Must match AFTER targets (what you requested)
        $eps = 0.01;
        if (abs($fromTotal - $fromTarget) > $eps || abs($toTotal - $toTarget) > $eps) {
            throw ValidationException::withMessages([
                'FromAllocations' => "From monthly total (".number_format($fromTotal,2).") must equal post-reallocation target ".number_format($fromTarget,2).".",
                'ToAllocations'   => "To monthly total (".number_format($toTotal,2).") must equal post-reallocation target ".number_format($toTarget,2).".",
            ]);
        }

        // 5) Look up ledger ids for both lines
        $fromLine = BudgetLine::findOrFail($fromLineId);
        $toLine   = BudgetLine::findOrFail($toLineId);
        $fromLineLedgerCBSAccID='';
        $toLineLedgerCBSAccID='';

        if (empty($fromLine->LedgerID)) {
            throw ValidationException::withMessages(['FromBudgetLineID' => 'Selected From line has no LedgerID configured.']);
        }else{
            $fromLineLedgerCBSAccID=BudgetGLMaster::where('BudgetGLID',$fromLine->BudgetGLAccountID)->first()->AccountID;
        }
        if (empty($toLine->LedgerID)) {
            throw ValidationException::withMessages(['ToBudgetLineID' => 'Selected To line has no LedgerID configured.']);
        }else{
            $toLineLedgerCBSAccID=BudgetGLMaster::where('BudgetGLID',$fromLine->BudgetGLAccountID)->first()->AccountID;
        }

        // 6) Persist everything atomically
        $realloc = DB::transaction(function () use (
            $validated, $fromMonthly, $toMonthly, $months, $fromLine, $toLine
        ) {
            // a) Reallocation header
            $realloc = BudgetReallocation::create([
                'BudgetID'         => $validated['BudgetID'],
                'FromBudgetLineID' => $validated['FromBudgetLineID'],
                'ToBudgetLineID'   => $validated['ToBudgetLineID'],
                'FromActivityID'   => $validated['FromActivityID'] ?? null,
                'ToActivityID'     => $validated['ToActivityID'] ?? null,
                'BranchID'         => $validated['BranchID'] ?? null,
                'DepartmentID'     => $validated['DepartmentID'] ?? null,
                'ReallocationType' => $validated['ReallocationType'],
                'Amount'           => $validated['Amount'],
                'Justification'    => $validated['Justification'],
                'Status'           => 'Pending',
                'CreatedBy'        => auth()->id(),
                'CreatedOn'        => now(),
            ]);

            // Helper to insert pending monthly limits
            $insertLimits = function(array $arr, BudgetLine $line, int $reallocId) use ($validated, $months) {
                foreach ($arr as $idx => $val) {
                    if ($val === null || $val === '' || (float)$val <= 0) continue;

                    // $idx is 1..12 per your field names (e.g., FromAllocations[9])
                    $month = $months[(int)$idx] ?? null;
                    if (!$month instanceof Carbon) continue;

                    BudgetLineLedgerLimit::create([
                        'BudgetID'       => $validated['BudgetID'],
                        'ReallocationID' => $reallocId,
                        'BudgetLineID'   => $line->Id, // assumes PK is Id per your schema
                        'ERPLedgerID'    => $line->BudgetGLAccountID ?? null,
                        'LedgerID'       => BudgetGLMaster::where('BudgetGLID',$line->BudgetGLAccountID)->first()->AccountID,   // required
                        'BranchID'       => $validated['BranchID'],
                        'LimitType'      => 'Monthly',
                        'LimitAmount'    => round((float)$val, 2),
                        'EffectiveFrom'  => $month->copy()->startOfMonth()->toDateString(),
                        'EffectiveTo'    => $month->copy()->endOfMonth()->toDateString(),
                        'IsActive'       => false, // pending approval
                        'CreatedBy'      => auth()->id(),
                        'CreatedOn'      => now(),
                    ]);
                }
            };

            // b) Pending monthly limits for FROM (post-reallocation distribution)
            $insertLimits($fromMonthly, $fromLine, $realloc->id);

            // c) Pending monthly limits for TO (post-reallocation distribution)
            $insertLimits($toMonthly, $toLine, $realloc->id);

            return $realloc;
        });

        return redirect()
            ->route('budgetandanalytics.reallocation.index')
            ->with('success', 'Reallocation submitted for approval. Ref #'.$realloc->id);
    }

    /**
     * Approve and apply the reallocation
     */
    public function approve($id)
    {
        $realloc = BudgetReallocation::findOrFail($id);

        // Deduct from source
        $this->adjustLineAmount($realloc->FromBudgetLineID, -$realloc->Amount, $realloc->BranchID);

        // Add to target
        $this->adjustLineAmount($realloc->ToBudgetLineID, $realloc->Amount, $realloc->BranchID);

        $realloc->Status = 'Approved';
        $realloc->save();

        return back()->with('success', 'Reallocation approved and budget lines updated.');
    }

    /**
     * Adjust budget line (activity-driven or manual) by amount
     */
    private function adjustLineAmount($lineId, $amount, $branchId = null)
    {
        // Activity driven?
        $isActivityDriven = BudgetActivityMaster::where('BudgetLineID', $lineId)
            ->where('IsActive', 1)
            ->exists();

        if ($isActivityDriven) {
            $activities = BudgetActivity::where('BudgetLineID', $lineId)
                ->when($branchId, fn($q) => $q->where('BranchID', $branchId))
                ->get();

            foreach ($activities as $activity) {
                $activity->FullAllocation += $amount;
                $activity->save();
                // optionally adjust monthly splits proportionally
            }
        } else {
            $entries = BudgetManualEntry::where('BudgetLineID', $lineId)
                ->when($branchId, fn($q) => $q->where('BranchID', $branchId))
                ->get();

            foreach ($entries as $entry) {
                $entry->Amount += $amount;
                $entry->save();
            }
        }
    }

    /**
     * AJAX: Fetch budget lines by department (and optional branch)
     */
    public function getBudgetLines($deptId, $branchId = null)
    {
        $lines = BudgetLine::where('DepartmentID', $deptId)->get();
        return response()->json($lines);
    }

    /**
     * AJAX: Fetch allocations/activities for a given budget line
     */
    public function getBudgetLineDetails($lineId, $branchId = null)
    {
        $activities = BudgetActivityMaster::where('BudgetLineID', $lineId)
            ->where('IsActive', 1)
            ->get();

        if ($activities->count() > 0) {
            $result = [];

            foreach ($activities as $activity) {
                $allocations = BudgetActivity::where('BudgetLineID', $lineId)
                    ->where('ActivityID', $activity->Id)
                    ->when($branchId, fn($q) => $q->where('BranchID', $branchId))
                    ->with('monthlyAllocations')
                    ->get();

                $monthly = [];
                foreach ($allocations as $alloc) {
                    foreach ($alloc->monthlyAllocations as $m) {
                        $monthly[] = [
                            'month'  => $m->Month,
                            'amount' => $m->Amount
                        ];
                    }
                }

                $result[] = [
                    'id'                  => $activity->Id,
                    'name'                => $activity->ActivityName,
                    'description'         => $activity->Description,
                    'monthly_allocations' => $monthly
                ];
            }

            return response()->json([
                'type'       => 'activity',
                'activities' => $result
            ]);
        }

        // Manual-driven lines
        $entries = BudgetManualEntry::where('BudgetLineID', $lineId)
            ->when($branchId, fn($q) => $q->where('BranchID', $branchId))
            ->with('allocations')
            ->get();

        $result = [];
        foreach ($entries as $entry) {
            $monthly = [];
            foreach ($entry->allocations as $m) {
                $monthly[] = [
                    'month'  => $m->Month,
                    'amount' => $m->Allocation
                ];
            }

            $result[] = [
                'id'                  => $entry->Id,
                'amount'              => $entry->Amount,
                'monthly_allocations' => $monthly
            ];
        }

        return response()->json([
            'type'    => 'manual',
            'entries' => $result
        ]);
    }

    public function getAllocations(Request $request)
    {
        $validated = $request->validate([
            'BudgetID'     => 'required|exists:t_Budgets,Id',
            'BranchID'     => 'required|exists:t_Branches,Id',
            'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
        ]);

        //Check the start and end of the budget fiscal year
        $budget=Budget::find($validated['BudgetID']);
        $data=array();

        $fromDate=$budget->From;
        $toDate=$budget->To;
        //Check the months in between from and to date
        // Convert the dates into Carbon instances (useful for date manipulation)
        $fromDate = \Carbon\Carbon::parse($fromDate);
        $toDate = \Carbon\Carbon::parse($toDate);

        // Initialize an array to store the months
        $months = [];

        // Loop through the range and get each month
        $currentDate = $fromDate;
        while ($currentDate <= $toDate) {
            // Push the current month to the array (you can use format('F Y') for full month names)
            $months[] = $currentDate->format('M Y');  // Example format: '2024-01'

            // Move to the next month
            $currentDate->addMonth();
        }

        //Check if the Line is Activity driven so as to know where to fetch the Total amount from
//        $check=BudgetActivityMaster::where('BudgetLineID',$validated['BudgetLineID'])->where('IsActive',true)->exists();
//        if($check){ //Is activity driven
//
//        }else{ //We pick from Manul entry by line
//
//        }

        //Pick the total amount from the Limits table for thst line
        $totAmountAllocated=BudgetLineLedgerLimit::where('BudgetLineID',$validated['BudgetLineID'])
            ->where('BudgetID',$validated['BudgetID'])
            ->where('BranchID',$validated['BranchID'])
            ->sum('LimitAmount');

        //Get the Usage for that GL so far
        //Get branch ID interms of CBT and now date
        $b_id=Branch::find($validated['BranchID'])->BranchID;
        $asDate = Carbon::createFromFormat('Y-m-d', '2024-04-30')->format('d M Y');
        // Call stored procedure and get the result
        $result = DB::select(
            'EXEC dbo.sp_GetBudgetLineClosingBalance ?, ?, ?, ?',
            [$validated['BudgetLineID'], $b_id, $asDate,'L']
        );

        // $result is an array of objects
        $totUsage =($result[0]->ClosingBalance =='.00'?0.00:$result[0]->ClosingBalance) ?? 0.00;

        //Push all the data collected in the data array
        $data[]=[
            'months'=>$months,
            'totAmountAllocated'=>$totAmountAllocated,
            'totUsage'=>abs($totUsage),
        ];


        return response()->json($data);

    }


}
