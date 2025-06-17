<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetLine;
use Illuminate\Http\Request;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetPeriodTypes;
use App\Models\Budget\BudgetProductType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetPeriodController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetPeriods::class);
        
        $periods=BudgetPeriods::with('periodTypeID')->get();
        

        return view('budgetandanalytics.budgetperiod.index', compact('periods'));
    }

    public function create()
    {
        $types=BudgetPeriodTypes::all();
        return view('budgetandanalytics.budgetperiod.create',compact('types'));
    }

    public function store(Request $request){
        $validated=$request->validate([
        'fiscalYear'  => 'required|string|max:10',
        'periodType'  => 'required|exists:t_BudgetPeriodTypes,Id',
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
                ->withProperties(['action' => 'create'])
                ->log('create periods');
        return redirect()->route('budgetperiod.index')->with('success', 'Budget Period  created successfully.');
        }catch(\Throwable $th){
            DB::rollBack();
            Log::error('Failed to create period:' . $th->getMessage());

            return back()->withErrors(['error'=>'Failed to create Period'])->withInput();
        }
    }

    public function edit($id){
        $periods=BudgetPeriods::findOrFail($id);
        $types=BudgetPeriodTypes::all();
        return view('budgetandanalytics.budgetperiod.edit', compact('periods','types'));
    }

    public function update(Request $request, $id){

        $this->authorize(PermissionEnum::BudgetSetupUpdate , BudgetPeriods::class);

        $validated=$request->validate([
        'fiscalYear'  => 'required|string|max:10',
        'periodType'  => 'required|exists:t_BudgetPeriodTypes,Id',
        'notes'       => 'nullable|string',
    ]);

    DB::beginTransaction();

    try{
        $period=BudgetPeriods::findOrFail($id);

        $period->update([
            'fiscalYear'  => $validated['fiscalYear'],
            'periodType'  => $validated['periodType'],
            'notes'       => $validated['notes'],
            'CreatedBy' =>Auth::Id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        DB::commit();
        activity()
                ->performedOn($period)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Period');

                return redirect()->route('budgetperiod.index')->with('success' , 'Period updated successfully');
    }catch(\Throwable $th){
        DB::rollBack();
        Log::error('Failed to Update period:' . $th->getMessage());

        return back()->withErrors(['error'=>'Failed to update period'])->withInput();
    }

    }

    public function destroy(string $id){
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetPeriods::class);
        try{
            $period = BudgetPeriods::findOrFail($id); // safer: throws 404 if not found
            $period->delete();

            activity()
                    ->performedOn(new BudgetPeriods())
                    ->causedBy(Auth::user())
                    ->withProperties(['action'=>'Delete'])
                    ->log('Deleted Period Successfully:'.$id);

            return redirect()->route('budgetperiod.index')->with('Success', 'Period Deleted Successfully');
        }catch(\Throwable $th){
            Log::error('---DELETE PERIOD ERROR---' . $th->getMessage());
             return redirect()->route('budgetperiod.index')->with('error', 'Failed to delete Period. Please try again.');
        }
    }

}
