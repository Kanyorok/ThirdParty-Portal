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
            'budgetLine:Id,LineName',])
            ->get();

            $groupedEntries = $entries->groupBy('BudgetID');

        return view('budgetandanalytics.budgetworkspace.entrybyglline.index', [ 'groupedEntries'=> $groupedEntries ]);
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
    $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetManualEntry::class);
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
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetManualEntry::class);
        $entries = BudgetManualEntry::with([
            'budgetLine:Id,LineName',
            'allocations:Id,Month,Allocation,EntryID',
        ])->where('BudgetID',$id)->get();

        return view('budgetandanalytics.budgetworkspace.entrybyglline.show', compact('entries'));
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetManualEntry::class);

        
        DB::beginTransaction();
        try {
            $entry = BudgetManualEntry::findOrFail($id);
            $monthlydelete = BudgetManualEntryAllocations::where('EntryId', $id)->update([
                'DeletedBy' =>  Auth::Id()
            ]);
            $monthlydelete = BudgetManualEntryAllocations::where('EntryId', $id)->delete();
            $entry->DeletedBy = Auth :: Id();
            $entry->save();
           
            $entry->delete(); // Delete the entry itself
            DB::commit();
            activity()
                ->performedOn($entry)
                ->causedBy(Auth::user())
                ->event('delete')
                ->withProperties(['action' => 'delete'])
                ->log('Deleted a Manual Budget Line Entry');
            return back()->with('success', 'Manual Budget Line Entry Deleted Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            Log::error('Failed to delete budget entry.', $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to delete budget entry. ' . $e->getMessage()]);
        }
    }

    public function glview($budgetId)
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetManualEntry::class);

        $entries = BudgetManualEntry::with([
            'branch:Id,Name',
            'budgetLine:Id,LineName',
            'allocations', // eager load allocations
        ])
        ->where('BudgetID', $budgetId)
        ->get();

        return view('budgetandanalytics.budgetworkspace.entrybyglline.glview', compact('entries'));
    }

    public function edit($id)
    {
        $entry = BudgetManualEntry::with(['budget:Id,Name', 'branch:Id,Name', 'budgetLine:Id,LineName', 'allocations'])->findOrFail($id);
        $branches = Branch::select('Id','Name')->get();
        $budgetLines = BudgetLine::select('Id','LineName')->get();
        return view('budgetandanalytics.budgetworkspace.entrybyglline.edit', compact('entry', 'branches', 'budgetLines'));
    }

    public function update(Request $request, $id)
{
    $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetManualEntry::class);

    $request->validate([
        'BranchID' => 'required|exists:t_Branches,Id',
        'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
        'Amount' => 'required|numeric|min:0',
        'monthly_allocations' => 'required|array',
        'monthly_allocations.*' => 'nullable|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        $entry = BudgetManualEntry::with('allocations')->findOrFail($id);
        $userId = Auth::id();

        $entry->update([
            // 'BudgetID' => $entry->BudgetID, // BudgetID should not be changed
            'BranchID' => $request->BranchID,
            'BudgetLineID' => $request->BudgetLineID,
            'Amount' => $request->Amount,
            'Comments' => $request->Comments,
            'ModifiedBy' => $userId,
            'ModifiedOn' => now(),
        ]);

        // Update allocations
        $entry->allocations()->delete();
        foreach ($request->monthly_allocations as $month => $allocation) {
            BudgetManualEntryAllocations::create([
                'EntryID' => $entry->Id,
                'BudgetID' => $entry->BudgetID,
                'Month' => str_pad($month, 2, '0', STR_PAD_LEFT),
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
            ->event('update')
            ->withProperties(['action' => 'update'])
            ->log('Updated a Manual Budget Line Entry');
        return redirect()->route('entrybyglline.index')->with('success', 'Manual Budget Line Entry Updated Successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update budget entry.', [
            'error' => $e->getMessage(),
            'stack' => $e->getTraceAsString()
        ]);
        return redirect()->back()->withErrors(['error' => 'Failed to update budget entry. ' . $e->getMessage()]);
    }
}
}

