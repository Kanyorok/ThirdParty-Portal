<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetDriverProjections;
use App\Models\Budget\BudgetDriverProjectionsData;
use App\Models\Budget\BudgetMonthlyProjectionAllocation;
use App\Models\Budget\BudgetProduct;
use App\Models\Core\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetProjectionsController extends Controller
{
    // View budget entry list
    public function index()
    {
        //$projections = BudgetDriverProjections::with(['scenario', 'product', 'period'])->get();
        $projections = BudgetDriverProjections::with(
            'projections',
            'budget:Id,Name',
            'currency:Id,Code',
        // 'period:Id,fiscalYear',
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
        $budgets = Budget::all();
        $currencies = Currency::all();
        $products = BudgetProduct::all();
        // $periods = BudgetPeriods::all();

        return view('budgetandanalytics.budgetworkspace.entry.create', compact(
            'budgets', 'currencies', 'products', // 'periods'
        ));
    }

    // Store budget product entry
    public function store(Request $request)
    {
        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'CurrencyID' => 'required|exists:t_Currencies,Id',
            // 'PeriodID'  => 'required|exists:t_BudgetPeriods,Id',
            'Products' => 'required|array|min:1',
            'Products.*.ProductID' => 'required|exists:t_BudgetProductTypes,Id',
            'Products.*.Volume' => 'required|integer|min:0',
            'Products.*.Value' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $ScenarioId = $validated['BudgetID'];
            $CurrencyId = $validated['CurrencyID'];
            // $PeriodId   = $validated['PeriodID'];

            //Store T1
            $projection = BudgetDriverProjections::create([
                'BudgetID' => $ScenarioId,
                'CurrencyID' => $CurrencyId,
                // 'PeriodID' => $PeriodId,
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

            return redirect()->route('budgetprojections.index')
                ->with('success', 'Driver Projections created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error Adding Projection: ' . $th->getMessage());

            return back()->withErrors(['Error' => 'Failed to Add Projection: ' . $th->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {

        $this->authorize(PermissionEnum::BudgetSetupView, BudgetMonthlyProjectionAllocation::class);

        $budget = BudgetDriverProjections::findOrFail($id);

        // Fetch all allocations so the view can filter and display as needed
        $monthlyAllocations = BudgetMonthlyProjectionAllocation::where('BudgetProjectionID', $id)
            ->get();

        return view('budgetandanalytics.budgetworkspace.entry.show', compact('monthlyAllocations', 'budget'));
    }

    public function edit($id)
    {
        return view('budgetandanalytics.budgetworkspace.entry.edit');
    }
}
