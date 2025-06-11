<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Budget\BudgetRates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BudgetRatesController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetRates::class);
         $rates = BudgetRates::all();
        return view('budgetandanalytics.settings.rates.index', compact('rates'));
    }

    public function create(){
        return view('budgetandanalytics.settings.rates.create');
    }

    public function store(request $request){
        $validated =$request->validate([
        'RateTypeCode' => 'required|string|max:50|unique:t_BudgetRates,RateTypeCode',
        'RateTypeName' => 'required|string|max:100',
        'Description' => 'nullable|string',
        'IsDefault' => 'nullable|boolean',
    ]);

    DB::beginTransaction();

    try{
       $rate = BudgetRates::create([
            'RateTypeCode' => $validated['RateTypeCode'],
            'RateTypeName' => $validated['RateTypeName'],
            'Description' => $validated['Description'],
            'IsDefault' => $validated['IsDefault'],
            'CreatedBy' =>Auth::Id(),
            'ModifiedBy' =>Auth::Id(),
        ]);
        DB::commit();

        activity()
            ->performedOn(new BudgetRates())
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'Create'])
            ->log('Create period types');

        return redirect()->route('rates.index')->with('success', 'Budget Rate created successfully.');
    }catch(\Throwable $th){
        DB::rollback();

        Log::error('Failed To Create Budget Rate:' .$th->getMessage());

        return back()->withErrors(['error' => 'Failed to create budget rate']);
    }
    }

     public function edit($id){
        $rate=BudgetRates::find($id);
        return view('budgetandanalytics.settings.rates.edit', compact('rate'));
    }

    public function update(Request $request, $id){
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetRates::class);

        $validated=$request->validate([
            'RateTypeCode' => 'required|string|max:50|unique:t_BudgetRates,RateTypeCode',
            'RateTypeName' => 'required|string|max:100',
            'Description' => 'nullable|string',
            'IsDefault' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try{
            $rate=BudgetRates::findOrFail($id);

            $rate->update([
                'RateTypeCode' => $validated['RateTypeCode'],
                'RateTypeName' => $validated['RateTypeName'],
                'Description' => $validated['Description'],
                'IsDefault' => $validated['IsDefault'],
                'CreatedBy' =>Auth::Id(),
                'ModifiedBy' =>Auth::Id(),
            ]);
             
            DB::commit();

            activity()
                ->performedOn($rate)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Rate');

            return redirect()->route('rates.index')->with('success','Rate Updated Successfully');
        }catch(\Throwable $th){
            DB::rollBack();

            Log::error('Failed to update rate:' . $th->getMessage());

            return back()->withErrors(['Errors'=>'Failed to Update rate'])->withInput();
        }
    }


    public function destroy(string $id){
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetRates::class);
        try{
            $rate=BudgetRates::find($id)->delete();
            //$rate->delete();

            activity()
                    ->performedOn(new BudgetRates())
                    ->causedBy(Auth::user())
                    ->withProperties(['action'=>'Delete'])
                    ->log('Deleted Rate Successfully:'.$id);

            return redirect()->route('rates.index')->with('Success', 'Rate Deleted Successfully');
        }catch(\Throwable $th){
            Log::error('---DELETE RATE ERROR---' . $th->getMessage());
            Log::error($th);
             return redirect()->route('rates.index')->with('error', 'Failed to delete Rate. Please try again.');
        }
    }

}
