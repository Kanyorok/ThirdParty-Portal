<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetPlanningMethods;
use App\Models\Budget\BudgetScenarioPlanning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetSceneriosController extends Controller
{
    //
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView , BudgetScenarioPlanning::class);
        $scenarios=BudgetScenarioPlanning::with(['budgetPeriodRef', 'planningMethodRef'])->get();
        return view('budgetandanalytics.scenarioplanning.index', compact('scenarios'));
    }

    public function create()
    {
        $periods=BudgetPeriods::all();
        $methods=BudgetPlanningMethods::all();
        return view('budgetandanalytics.scenarioplanning.create', compact('methods','periods'));
    }

    public function store(request $request){
     $validated = $request->validate([
        'scenarioName'    => 'required|string|max:100',
        'description'     => 'nullable|string',
        'budgetPeriod'    => 'required|exists:t_BudgetPeriods,Id', // assuming it's an ID or year
        'planningMethod'  => 'required|exists:t_BudgetPlanningMethods,Id',
        //'isDefault'       => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
        $scenario = BudgetScenarioPlanning::create([
            'scenarioName'    => $validated['scenarioName'],
            'description'     => $validated['description'] ?? null,
            'budgetPeriod'    => $validated['budgetPeriod'],
            'planningMethod'  => $validated['planningMethod'],
            'isDefault'       => $request->isDefault=='on' ?true:false,
            'CreatedBy'       => Auth::Id(),
            'ModifiedBy'      => Auth::Id(),
        ]);

        DB::commit();
         activity()
            ->performedOn(new BudgetScenarioPlanning())
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'Create'])
            ->log('Create Scennarios');

            return redirect()->route('budgetscenerios.index')->with('Success','Scenario successfully created');
    }catch(\Throwable $th){
        DB::rollBack();
return $th->getMessage();
        Log::error('Fialed to create scenario:' . $th->getMessage());
         return back()->withErrors(['error' => 'Failed to create budget scenario']);
    }
}

    public function edit($id){
        $scenario=BudgetScenarioPlanning::findOrFail($id);
        $periods=BudgetPeriods::all();
        $methods=BudgetPlanningMethods::all();

        return view('budgetandanalytics.scenarioplanning.edit', compact('scenario', 'periods','methods'));
    }

    public function update(Request $request, $id){
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetScenarioPlanning::class);

        $validated=$request->validate([
            'scenarioName'    => 'required|string|max:100',
            'description'     => 'nullable|string',
            'budgetPeriod'    => 'required|exists:t_BudgetPeriods,Id', // assuming it's an ID or year
            'planningMethod'  => 'required|exists:t_BudgetPlanningMethods,Id',
            'isDefault'       => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try{
            $scenario=BudgetScenarioPlanning::findOrFail($id);

            $scenario->update([
                'scenarioName'    => $validated['scenarioName'],
                'description'     => $validated['description'] ?? null,
                'budgetPeriod'    => $validated['budgetPeriod'],
                'planningMethod'  => $validated['planningMethod'],
                'isDefault'       => $request->isDefault=='on' ?true:false,
                'CreatedBy'       => Auth::Id(),
                'ModifiedBy'      => Auth::Id(),
            ]);
             
            DB::commit();

            activity()
                ->performedOn($scenario)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Scenario');

            return redirect()->route('budgetscenerios.index')->with('success','Scenario Updated Successfully');
        }catch(\Throwable $th){
            DB::rollBack();

            Log::error('Failed to update scenario:' . $th->getMessage());

            return back()->withErrors(['Errors'=>'Failed to Update Scenario'])->withInput();
        }
    }

    public function destroy(string $id){
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetScenarioPlanning::class);
        try{
            $scenario=BudgetScenarioPlanning::find($id)->delete();
            //$scenario->delete();

            activity()
                    ->performedOn(new BudgetScenarioPlanning())
                    ->causedBy(Auth::user())
                    ->withProperties(['action'=>'Delete'])
                    ->log('Deleted Scenario Successfully:'.$id);

            return redirect()->route('budgetscenerios.index')->with('Success', 'Scenario Deleted Successfully');
        }catch(\Throwable $th){
            Log::error('---DELETE SCENARIO ERROR---' . $th->getMessage());
             return redirect()->route('budgetscenerios.index')->with('error', 'Failed to delete Scenario. Please try again.');
        }
    }

}
