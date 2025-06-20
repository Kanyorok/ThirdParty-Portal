<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetLine;
use Illuminate\Http\Request;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetPeriodTypes;
use App\Models\Budget\BudgetProductType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetPeriodController extends Controller
{
    //
public function index()
{
    $this->authorize(PermissionEnum::BudgetSetupView, BudgetPeriods::class);

    $budgets = Budget::all()->map(function ($budget) {
        $today = Carbon::today();
        $from = $budget->From ? Carbon::parse($budget->From) : null;
        $to = $budget->To ? Carbon::parse($budget->To) : null;

        $status = 'Unknown';
        $badgeClass = 'secondary';

        if ($from && $to) {
            if ($today->between($from, $to)) {
                $status = 'Open';
                $badgeClass = 'success';
            } elseif ($today->lt($from)) {
                $status = 'Upcoming';
                $badgeClass = 'info';
            } elseif ($today->gt($to)) {
                $status = 'Expired';
                $badgeClass = 'danger';
            }
        } elseif ($to && $today->gt($to)) {
            $status = 'Expired';
            $badgeClass = 'danger';
        } elseif ($from && $today->lt($from)) {
            $status = 'Upcoming';
            $badgeClass = 'info';
        }

        // Append computed values
        $budget->status = $status;
        $budget->badgeClass = $badgeClass;

        return $budget;
    });

    return view('budgetandanalytics.budgetperiod.index', compact('budgets'));
}

    public function create()
    {
        $types = BudgetPeriodTypes::all();
        return view('budgetandanalytics.budgetperiod.create', compact('types'));
    }

    public function store(Request $request){
        $validated=$request->validate([
        'fiscalYear'  => 'required|string|max:10',
        'periodType'  => 'required|string|max:20',
        'notes'       => 'nullable|string',
        ]);

        DB::beginTransaction();

        try{
            $period=BudgetPeriods::create([
            'fiscalYear'  => $validated['fiscalYear'],
            'periodType'  => $validated['periodType'],
            'notes'       => $validated['notes'],
            'CreatedBy' =>Auth::Id(),
            'ModifiedBy' => Auth::Id()
            ]);

            DB::commit();

            activity()
             ->performedOn(new BudgetPeriods())
                ->causedBy(Auth::user())
                ->event('create')
                ->withProperties(['action' => 'create'])
                ->log('create periods');
        return redirect()->route('budgetperiod.index')->with('success', 'Budget Period  created successfully.');
        }catch(\Throwable $th){
            DB::rollBack();
            Log::error('Failed to create budget: ' . $th->getMessage());

            return back()->withErrors(['error'=>'Failed to create Period'])->withInput();
        }
    }

    public function edit($id)
    {
        $periods = BudgetPeriods::findOrFail($id);
        $types = BudgetPeriodTypes::all();
        return view('budgetandanalytics.budgetperiod.edit', compact('periods', 'types'));
    }

    public function update(Request $request, $id)
    {

        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetPeriods::class);

        $validated=$request->validate([
        'fiscalYear'  => 'required|string|max:10',
        'periodType'  => 'required|string|max:20',
        'notes'       => 'nullable|string',
    ]);

        DB::beginTransaction();

        try {
            $period = BudgetPeriods::findOrFail($id);

            $period->update([
                'fiscalYear' => $validated['fiscalYear'],
                'periodType' => $validated['periodType'],
                'notes' => $validated['notes'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($period)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Period');

            return redirect()->route('budgetperiod.index')->with('success', 'Period updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update period:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update period'])->withInput();
    }

    }

    public function destroy(string $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetPeriods::class);
        try{
            $period=BudgetPeriods::find($id)->delete();
            //$period->delete();

            activity()
                ->performedOn(new BudgetPeriods())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'Delete'])
                ->log('Deleted Period Successfully:' . $id);

            return redirect()->route('budgetperiod.index')->with('Success', 'Period Deleted Successfully');
        } catch (\Throwable $th) {
            Log::error('---DELETE PERIOD ERROR---' . $th->getMessage());
            return redirect()->route('budgetperiod.index')->with('error', 'Failed to delete Period. Please try again.');
        }
    }

}
