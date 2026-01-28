<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetDriverProjections;
use App\Models\Budget\BudgetDriverProjectionsData;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetProductType;
use App\Models\Budget\BudgetScenarioPlanning;
use App\Models\Core\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BudgetProductEntryController extends Controller
{
    // View budget entry list
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetDriverProjections::class);
        $projections = BudgetDriverProjections::with(
            'projections',
            'scenario:Id,scenarioName',
            'currency:Id,Code',
            'period:Id,fiscalYear',
        )->get();

        // Compute totals for each main projection
        foreach ($projections as $proj) {
            $proj->total_volume = $proj->projections->sum(function ($item) {
                return (float)$item->Volume;
            });
            $proj->total_value = $proj->projections->sum(function ($item) {
                return (float)$item->Value;
            });
        }

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
            'scenarios',
            'currencies',
            'products',
            'periods'
        ));
    }

    // Store budget product entry
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ScenarioID' => 'required|exists:t_BudgetScenarioPlanning,Id',
            'CurrencyID' => 'required|exists:t_Currencies,Id',
            'PeriodID' => 'required|exists:t_BudgetPeriods,Id',
            'Products' => 'required|array|min:1',
            'Products.*.ProductID' => 'required|exists:t_BudgetProductTypes,Id',
            'Products.*.Volume' => 'required|integer|min:0',
            'Products.*.Value' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $ScenarioId = $validated['ScenarioID'];
            $CurrencyId = $validated['CurrencyID'];
            $PeriodId = $validated['PeriodID'];

            //Store T1
            $projection = BudgetDriverProjections::create([
                'ScenarioID' => $ScenarioId,
                'CurrencyID' => $CurrencyId,
                'PeriodID' => $PeriodId,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);



            foreach ($validated['Products'] as $product) {
                //Store t2
                $projectiondata = BudgetDriverProjectionsData::create([
                    'BudgetDriverProjectionsID' => $projection->Id,
                    'ProductID' => $product['ProductID'],
                    'Volume' => $product['Volume'],
                    'Value' => $product['Value'], // fixed casing
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
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
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error('Error Adding Projection: ' . $th->getMessage());

            return back()->withErrors(['Error' => 'Failed to Add Projection: ' . $th->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        return view('budgetandanalytics.budgetworkspace.entry.show');
    }

    public function edit($id)
    {
        return view('budgetandanalytics.budgetworkspace.entry.edit');
    }
}
