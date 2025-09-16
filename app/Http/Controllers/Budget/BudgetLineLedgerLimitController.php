<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetLineLedgerLimit;
use App\Models\Procurement\BudgetMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetLineLedgerLimitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $limits = BudgetLineLedgerLimit::orderBy('CreatedOn', 'desc')->get();
        return view('budgetandanalytics.limits.index', compact('limits'));
    }

    public function create() {
        return view('budgetandanalytics.limits.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'BudgetLineID'=>'required',
            'LedgerID'=>'required',
            'LimitType'=>'required',
            'LimitAmount'=>'required|numeric|min:1',
            'EffectiveFrom'=>'required|date'
        ]);

//        BudgetLineLedgerLimit::create($validated + ['CreatedBy'=>auth()->id()]);
        //Get Ledger CBS ID
        $cbsID=BudgetMaster::where('BudgetGLID',$validated['BudgetLineID'])->pluck('AccountID')->first();
        BudgetLineLedgerLimit::create([
            'BudgetLineID'   => $validated['BudgetLineID'],
            'LedgerID'       => $cbsID,
            'ERPLedgerID'       => $validated['LedgerID'],
            'LimitType'      => $validated['LimitType'],
            'LimitAmount'    => $validated['LimitAmount'],
            'EffectiveFrom'  => $validated['EffectiveFrom'],
            'CreatedBy'      => auth()->id(),
        ]);
        return redirect()->route('budget.limits.index')->with('success','Ledger limit saved.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }


    public function showUpdateForm()
    {
        $budgets = Budget::where('Status', 'draft')->get();
        return view('budgetandanalytics.limits.update', compact('budgets'));
    }

    public function runUpdate(Request $request)
    {
        // Validate BudgetID
        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
        ]);

        $budgetId = $validated['BudgetID'];
        $budget = Budget::findOrFail($budgetId);
        $userId   = auth()->id();

        try {
            DB::transaction(function () use ($budgetId, $userId,$budget) {

                // Call stored procedure
                DB::statement(
                    'EXEC dbo.p_SyncLedgerLimitsFromBudget ?, ?',
                    [$budgetId, $userId]
                );

                // Update Budget record
                $budget->IsLimitSet = true;
                $budget->save();
            });

            return back()->with('success', "✅ Ledger limits successfully synced for Budget: $budget->Name.");

        } catch (\Throwable $e) {
            return $e->getMessage();
            Log::error('Ledger limit sync failed', [
                'budget_id' => $budgetId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', '⚠️ Failed to sync ledger limits. No changes were made.');
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
