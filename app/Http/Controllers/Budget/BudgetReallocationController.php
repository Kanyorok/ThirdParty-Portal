<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetActivity;
use App\Models\Budget\BudgetActivityMaster;
use App\Models\Budget\BudgetMonthlyAllocation;
use App\Models\Budget\BudgetReallocation;
use App\Models\Budget\BudgetManualEntry;
use Illuminate\Http\Request;

class BudgetReallocationController extends Controller
{
    public function index()
    {
        $reallocations = BudgetReallocation::orderBy('CreatedOn', 'desc')->get();
        return view('budgetandanalytics.reallocation.index', compact('reallocations'));
    }

    public function create()
    {
        $budgets     = Budget::where('Status', 'draft')->get();
        $branches    = \App\Models\Core\Branch::all();
        $departments = \App\Models\HRM\Department::all();
        $lines       = BudgetLine::all();

        return view('budgetandanalytics.reallocation.create',
            compact('budgets', 'branches', 'departments', 'lines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'BudgetID'         => 'required',
            'FromBudgetLineID' => 'required',
            'ToBudgetLineID'   => 'required',
            'Amount'           => 'required|numeric|min:1',
            'Justification'    => 'required|string',
            'ReallocationType' => 'required|string',
            'BranchID'         => 'nullable',
            'DepartmentID'     => 'nullable',
            'ToDepartmentID'   => 'nullable',
        ]);

        $realloc = BudgetReallocation::create($validated + [
            'CreatedBy' => auth()->id(),
            'Status'    => 'Pending'
        ]);

        return redirect()
            ->route('budgetandanalytics.reallocation.index')
            ->with('success', 'Reallocation request submitted.');
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
}
