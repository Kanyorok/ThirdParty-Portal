<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetManualEntry;
use App\Models\Budget\BudgetManualEntryAllocations;
use App\Models\Core\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetGLLineEntryController extends Controller
{
    //
    public function index()
    {

        $this->authorize(PermissionEnum::BudgetSetupView, BudgetManualEntry::class);

        $entries = BudgetManualEntry::with([
            'budget:Id,Name',
            'branch:Id,Name',
            'budgetLine:Id,LineName',])
            ->get();

        return view('budgetandanalytics.budgetworkspace.entrybyglline.index', compact('entries'));
    }

    public function create()
    {

        $budgets=Budget::select('Id','Name')->get();
        $branches=Branch::select('Id','Name')->get();
        $budgetLines=BudgetLine::select('Id','LineName')->get();

        return view('budgetandanalytics.budgetworkspace.entrybyglline.create',compact(
            'budgets',
            'branches',
            'budgetLines'
        ));
    }

public function store(Request $request)
{
    $request->validate([
        'BudgetID' => 'required|exists:t_Budgets,Id',
        'BranchID' => 'required|exists:t_Branches,Id',
        'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
        'Amount' => 'required|numeric|min:0',
        'monthly_allocations' => 'required|array',
        'monthly_allocations.*' => 'nullable|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        $userId = Auth::id();

        $entry = BudgetManualEntry::create([
            'BudgetID' => $request->BudgetID,
            'BranchID' => $request->BranchID,
            'BudgetLineID' => $request->BudgetLineID,
            'Amount' => $request->Amount,
            'Comments' => $request->Comments,
            'CreatedBy' => $userId,
            'CreatedOn' => now(),
            'ModifiedBy' => $userId,
            'ModifiedOn' => now(),
        ]);

        foreach ($request->monthly_allocations as $month => $allocation) {
                BudgetManualEntryAllocations::create([
                    'EntryID' => $entry->Id,
                    'BudgetID' => $request->BudgetID,
                    'Month' => $month,
                    'Allocation' => $allocation,
                    'CreatedBy' => $userId,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);
        }

        DB::commit();
            activity()
                ->performedOn($entry)
                ->causedBy(Auth::user())
                ->event('create')
                ->withProperties(['action' => 'create'])
                ->log('Created a Manual Budget Line Entry');
                return redirect()->route('entrybyglline.index')->with('success', 'Manual Budget Line Entry Created Successfully!');
    } catch (\Exception $e) {
        DB::rollback();
        return $e->getMessage();
        Log::error('Failed to save budget entry.', [
            'error' => $e->getMessage(),
            'stack' => $e->getTraceAsString()
        ]);
        return redirect()->back()->withErrors(['error' => 'Failed to save budget entry. ' . $e->getMessage()]);
    }
}

    public function show($id)
    {
        $entry = BudgetManualEntry::with([
            'budget:Id,Name',
            'branch:Id,Name',
            'budgetLine:Id,LineName',
        ])->findOrFail($id);

        $monthlyAllocations = BudgetManualEntryAllocations::where('EntryID', $id)->get();
        return view('budgetandanalytics.budgetworkspace.entrybyglline.show', compact('entry', 'monthlyAllocations'));
    }

}

