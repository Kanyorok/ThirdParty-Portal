<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetDriverProjections;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetProductType;
use App\Models\Budget\BudgetScenarioPlanning;
use App\Models\Core\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetProductEntryController extends Controller
{
    // View budget entry list
   public function index()
    {
        //$projections = BudgetDriverProjections::with(['scenario', 'product', 'period'])->get();
        $projections=[];

        return view('budgetandanalytics.budgetworkspace.entry.index', compact('projections'));
    }


    // Show form for new entry
    public function create()
    {
        $scenarios = BudgetScenarioPlanning::all();
        $currencies = Currency::all();
        $products = BudgetProductType::all();
        $periods = BudgetPeriods::all();

        return view('budgetandanalytics.budgetworkspace.entry.create', compact(
            'scenarios', 'currencies', 'products', 'periods'
        ));
    }

    // Store budget product entry
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ScenarioID' => 'required|exists:t_BudgetScenarioPlanning,Id',
            'CurrencyID' => 'required|exists:t_Currencies,Id',
            'Products'   => 'required|array|min:1',
            'Products.*.ProductID' => 'required|exists:t_BudgetProductTypes,Id',
            'Products.*.PeriodID'  => 'required|exists:t_BudgetPeriods,Id',
            'Products.*.Volume'    => 'required|integer|min:0',
            'Products.*.Value'     => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $ScenarioId = $validated['ScenarioID'];
            $CurrencyId = $validated['CurrencyID'];
            //$periodID=

            //Store T1
            //$t1=T1::create([
              //  $ScenarioId = $validated['ScenarioID'];
                //$CurrencyId = $validated['CurrencyID'];
                //$periodID=
           // ]);



            foreach ($validated['Products'] as $product) {
                //Store t2
                //$t2=T2::create([
                 //   'T1ID'=>$t1->Id,
                   // 'ProductID'  => $product['ProductID'],
                   // 'Volume'     => $product['Volume'],
                    //'Value'      => $product['Value'], // fixed casing
                    //'CreatedBy'  => Auth::id(),
                    //'ModifiedBy'  => Auth::id(),
               // ])

                $projection=BudgetDriverProjections::create([
                    'ScenarioID' => $ScenarioId,
                    'CurrencyID' => $CurrencyId,
                    'ProductID'  => $product['ProductID'],
                    'PeriodID'   => $product['PeriodID'],
                    'Volume'     => $product['Volume'],
                    'Value'      => $product['Value'], // fixed casing
                    'CreatedBy'  => Auth::id(),
                    'ModifiedBy'  => Auth::id(),
                ]);
            }

            DB::commit();

            activity()
                ->performedOn(new BudgetDriverProjections())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Driver Projections');

            return redirect()->route('entrybyproduct.index')
                ->with('success', 'Driver Projections created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error Adding Projection: ' . $th->getMessage());

            return back()->withErrors(['Error' => 'Failed to Add Projection: ' . $th->getMessage()])
                ->withInput();
        }
    }
}
