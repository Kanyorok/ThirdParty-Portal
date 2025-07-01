<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetDriverRates;
use App\Models\Budget\BudgetPeriodTypes;
use App\Models\Budget\BudgetProductType;
use App\Models\Budget\BudgetRates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class YieldRateController extends Controller
{
    //
    public function index()
    {
        $driverRates = BudgetDriverRates::with('productType', 'periodType', 'rateType')->get();

        $periodTypes = BudgetPeriodTypes::select('Id', 'PeriodType')->get();
        $productTypes = BudgetProductType::select('Id', 'Name')->get();
        $rateTypes = BudgetRates::select('Id', 'RateTypeName')->get();

        return view('budgetandanalytics.yieldexpenserate.index', compact(
            'driverRates',
            'periodTypes',
            'productTypes',
            'rateTypes',
        ));
    }

    public function create()
    {
        return view('budgetandanalytics.yieldexpenserate.create');
    }

    public function store(Request $request)
    {
        //Check permissions
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetDriverRates::class);

        //Validate request
        $validated = $request->validate([
            // 'PeriodTypeID'   => 'required|integer|exists:t_BudgetPeriodTypes,id',
            'ProductTypeID' => 'required|integer|exists:t_BudgetProductTypes,id',
            'RateTypeID' => 'required|integer|exists:t_BudgetRates,id',
            'RateValue' => 'required|numeric|min:0|max:100',
            // 'EffectiveDate'  => 'required|date',
            'Source' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $driverRate = BudgetDriverRates::create([
                'PeriodTypeID' => 1, //for nullable period type
                'ProductTypeID' => $validated['ProductTypeID'],
                'RateTypeID' => $validated['RateTypeID'],
                'RateValue' => $validated['RateValue'],
                'EffectiveDate' => 1, //for nullable effective date
                'Source' => $validated['Source'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id()
            ]);
            DB::commit();
            activity()
                ->performedOn($driverRate)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Budget Driver Rate');

            return back()->with('success', 'Driver created successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update period:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to create driver'])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetDriverRates::class);


        DB::beginTransaction();
        try {
            $yieldrate = BudgetDriverRates::findOrFail($id);
            $yieldrate->delete();
            DB::commit();
            activity()
                ->performedOn($yieldrate)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted Product Rate: ' . $yieldrate->ProductTypeID);

            return redirect()->route('yieldexpenserate.index')->with('success', 'Yield Expense Rate deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to delete Product Rate: ' . $th->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to delete Product Rate: ' . $th->getMessage()]);

        }
    }

    public function edit($id)
    {
        $driverRate = BudgetDriverRates::findOrFail($id);
        $periodTypes = BudgetPeriodTypes::select('Id', 'PeriodType')->get();
        $productTypes = BudgetProductType::select('Id', 'Name')->get();
        $rateTypes = BudgetRates::select('Id', 'RateTypeName')->get();

        return view('budgetandanalytics.yieldexpenserate.edit', compact(
            'driverRate',
            'periodTypes',
            'productTypes',
            'rateTypes'
        ));
    }

    public function update(Request $request, $id)
    {
        //Check permissions
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetDriverRates::class);

        //Validate request
        $validated = $request->validate([
            // 'PeriodTypeID'   => 'required|integer|exists:t_BudgetPeriodTypes,id',
            'ProductTypeID' => 'required|integer|exists:t_BudgetProductTypes,id',
            'RateTypeID' => 'required|integer|exists:t_BudgetRates,id',
            'RateValue' => 'required|numeric|min:0|max:100',
            // 'EffectiveDate'  => 'required|date',
            'Source' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            $driverRate = BudgetDriverRates::findOrFail($id);
            $driverRate->update([
                'PeriodTypeID' => 1,
                'ProductTypeID' => $validated['ProductTypeID'],
                'RateTypeID' => $validated['RateTypeID'],
                'RateValue' => $validated['RateValue'],
                'EffectiveDate' => 1,
                'Source' => $validated['Source'],
                'ModifiedBy' => Auth::id(),
            ]);
            DB::commit();
            activity()
                ->performedOn($driverRate)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Budget Driver Rate');

            return redirect()->route('yieldexpenserate.index')->with('success', 'Driver updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Driver Rate: ' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update driver'])->withInput();
        }
    }

}
