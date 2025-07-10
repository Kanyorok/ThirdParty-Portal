<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use Illuminate\Http\Request;
use App\Models\Budget\BudgetDriverProjections;
use App\Models\Budget\BudgetDriverProjectionsData;
use App\Models\Budget\BudgetMonthlyProjectionAllocation;
use App\Models\Budget\BudgetPeriods;
use App\Models\Budget\BudgetProduct;
use App\Models\Budget\BudgetProductType;
use App\Models\Budget\BudgetScenarioPlanning;
use App\Models\Core\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetProjectionsController extends Controller
{
    // View budget entry list
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetDriverProjections::class);
        //$projections = BudgetDriverProjections::with(['scenario', 'product', 'period'])->get();
        $projections = BudgetDriverProjections::with(
            'projections',
            'budget:Id,Name',
            'currency:Id,Code',
        // 'period:Id,fiscalYear',
        )->get();

        $groupedProjections = $projections->groupBy('BudgetID');

        // Compute totals for each main projection
        foreach ($projections as $proj) {
            $proj->total_volume = $proj->projections->sum(function ($item) {
                return (float)$item->Volume;
            });
            $proj->total_value = $proj->projections->sum(function ($item) {
                return (float)$item->Value;
            });
        }
        // return $groupedProjections;
        return view('budgetandanalytics.budgetworkspace.entry.index', compact(
            'groupedProjections'
        ));
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
        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetDriverProjections::class);
        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            // 'CurrencyID' => 'required|exists:t_Currencies,Id', (currently we will use 1 as currency)
            // 'PeriodID'  => 'required|exists:t_BudgetPeriods,Id',
            'Products' => 'required|array|min:1',
            'Products.*.ProductID' => 'required|exists:t_BudgetProductTypes,Id',
            'Products.*.Volume' => 'required|integer|min:0',
            // 'Products.*.Value'     => 'required|numeric|min:0', (till  futher notice, we will use 1 as value)
        ]);

        DB::beginTransaction();

        try {
            $ScenarioId = $validated['BudgetID'];
            $CurrencyId = 1;
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
                    'Value' => 1, // fixed casing
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

        $rate = BudgetProduct::with(['rate'])
            ->get();
        // Fetch all allocations so the view can filter and display as needed
        $monthlyAllocations = BudgetMonthlyProjectionAllocation::where('BudgetProjectionID', $id)
            ->get();

        return view('budgetandanalytics.budgetworkspace.entry.show', compact('monthlyAllocations', 'budget'));
    }

    public function edit($id)
    {
        $budget = BudgetDriverProjections::with(['projections', 'budget', 'currency'])->findOrFail($id);
        $monthlyAllocations = BudgetMonthlyProjectionAllocation::where('BudgetProjectionID', $id)->get();
        $currencies = Currency::all();
        $products = BudgetProduct::all();
        $productTypes = BudgetProductType::all();
        $months = [
            'Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6',
            'Month 7', 'Month 8', 'Month 9', 'Month 10', 'Month 11', 'Month 12'
        ];
        return view('budgetandanalytics.budgetworkspace.entry.edit', compact('budget', 'monthlyAllocations', 'currencies', 'products', 'months', 'productTypes'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetDriverProjections::class);
        $validated = $request->validate([
            // 'CurrencyID' => 'nullable|exists:t_Currencies,Id',
            'Products' => 'nullable|array',
            'Products.*.ProductID' => 'required_with:Products|exists:t_BudgetProductTypes,Id',
            'Products.*.Volume' => 'required_with:Products|integer|min:0',
            // 'Products.*.Value'     => 'required_with:Products|numeric|min:0',
            'MonthlyAllocations' => 'nullable|array',
            'MonthlyAllocations.*' => 'nullable|numeric|min:0',
        ]);
        DB::beginTransaction();
        try {
            // Log::info('BudgetProjectionsController update request', [
            //     'CurrencyID' => $request->input('CurrencyID'),
            //     'Products' => $request->input('Products'),
            //     'MonthlyAllocations' => $request->input('MonthlyAllocations'),
            // ]);
            $budget = BudgetDriverProjections::findOrFail($id);
            // Update currency if present
            if (isset($validated['CurrencyID'])) {
                $currencyId = 1;
                $budget->update([
                    'CurrencyID' => $currencyId,
                    'ModifiedBy' => Auth::id(),
                ]);
            }
            // Update products if present
            if (isset($validated['Products'])) {
                // Filter out any products with non-numeric ProductID
                $validProducts = array_filter($validated['Products'], function ($product) {
                    return isset($product['ProductID']) && is_numeric($product['ProductID']);
                });
                $budget->projections()->delete();
                foreach ($validProducts as $product) {
                    BudgetDriverProjectionsData::create([
                        'BudgetDriverProjectionsID' => $budget->Id,
                        'ProductID' => $product['ProductID'],
                        'Volume' => $product['Volume'],
                        'Value' => 1,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                    ]);
                }
            }
            // Update allocations if present
            if (isset($validated['MonthlyAllocations'])) {
                $existing = BudgetMonthlyProjectionAllocation::where('BudgetProjectionID', $id)->get()->keyBy('Month');
                foreach ($validated['MonthlyAllocations'] as $month => $amount) {
                    $month = (int)$month;
                    $amount = (float)$amount;
                    if (isset($existing[$month])) {
                        $existing[$month]->update([
                            'Allocation' => $amount,
                            'ModifiedBy' => Auth::id(),
                        ]);
                    } else {
                        BudgetMonthlyProjectionAllocation::create([
                            'BudgetID' => $budget->BudgetID,
                            'BudgetProjectionID' => $id,
                            'Month' => $month,
                            'Allocation' => $amount,
                            'CreatedBy' => Auth::id(),
                            'ModifiedBy' => Auth::id(),
                        ]);
                    }
                }
            }
            DB::commit();
            return redirect()->route('budgetprojections.index')->with('success', 'Budget entry updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['Error' => 'Failed to update: ' . $th->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetDriverProjections::class);
        DB::beginTransaction();
        try {
            $budget = BudgetDriverProjections::findOrFail($id);
            // Delete related projections and allocations
            $budget->projections()->delete();
            BudgetMonthlyProjectionAllocation::where('BudgetProjectionID', $id)->delete();
            $budget->delete();
            DB::commit();
            return redirect()->route('budgetprojections.index')->with('success', 'Budget projection deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to delete budget projection: ' . $th->getMessage());
            return back()->withErrors(['Error' => 'Failed to delete: ' . $th->getMessage()]);
        }
    }
}
