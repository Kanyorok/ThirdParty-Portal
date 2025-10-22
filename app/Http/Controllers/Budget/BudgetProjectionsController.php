<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetDriverProjections;
use App\Models\Budget\BudgetDriverProjectionsData;
use App\Models\Budget\BudgetDriverRates;
use App\Models\Budget\BudgetLine;
use App\Models\Budget\BudgetLineProductTypes;
use App\Models\Budget\BudgetMonthlyProjectionAllocation;
use App\Models\Budget\BudgetProduct;
use App\Models\Budget\BudgetProductType;
use App\Models\Budget\BudgetProjection;
use App\Models\Budget\BudgetProjectionData;
use App\Models\Core\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BudgetProjectionsController extends Controller
{
    // View budget entry list
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetDriverProjections::class);
        //New Approach to do the Budget Projection Read
        $budgetsIDS = BudgetProjection::distinct('BudgetID')->pluck('BudgetID')->toArray();
        $data = [];
        foreach ($budgetsIDS as $budgetID) {
            $budget = Budget::withoutTrashed()->find($budgetID);
            if (!$budget) continue;
            $data[] = [
                'Name' => $budget->Name,
                'ApprovalStatus' => $budget->Status,
                'Products' => BudgetProjection::where('BudgetID', $budgetID)->count(),
                'Accounts' => BudgetProjection::where('BudgetID', $budgetID)->sum('NumberOfAccounts'),
                'Id' => $budget->Id,
            ];
        }

        return view('budgetandanalytics.budgetworkspace.entry.index', compact(
            'data'
        ));
    }


    // Show form for new entry
    public function create()
    {
        $budgets = Budget::select('Id', 'Name')->where('Status', 'draft')->get();
        $currencies = Currency::all();
        $products = BudgetProduct::all();
        // $periods = BudgetPeriods::all();
        //Select the budget lines and their products driving them
        $budgetLines = BudgetLine::whereHas('productTypes')->with('productTypes')->get();

        return view('budgetandanalytics.budgetworkspace.entry.create', compact(
            'budgets', 'currencies', 'products', 'budgetLines' // 'periods'
        ));
    }

    public function getProductTypes($budgetLineId)
    {
        $productIDS = BudgetLineProductTypes::where('BudgetLineID', $budgetLineId)->pluck('ProductTypeID')->toArray();
        //Ensure that the prods being displ must have a Rate value entered.
        $productsIdWithRate = BudgetDriverRates::pluck('ProductTypeID')->toArray();
        $filteredProdID = array_intersect($productIDS, $productsIdWithRate);

        $product = BudgetProduct::whereIn('Id', $filteredProdID)->get();
        //$budgetLine = BudgetLine::with('products')->findOrFail($budgetLineId);
        return response()->json($product);
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
        ],
            [
                'Products.*.ProductID.required' => 'Please select a product',
                'Products.*.Volume.required' => 'Please enter a volume',
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

            activity()
                ->performedOn(new BudgetDriverProjections())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Driver Projections');

            DB::commit();

            return redirect()->route('budgetprojections.index')
                ->with('success', 'Driver Projections created successfully.');
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error('Error Adding Projection: ' . $th->getMessage());

            return back()->withErrors(['Error' => 'Failed to Add Projection: ' . $th->getMessage()])
                ->withInput();
        }
    }

    //Store projection with the 2nd model flow
    public function storeProjections(Request $request)
    {

        $this->authorize(PermissionEnum::BudgetSetupCreate, BudgetProjection::class);
        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
            'ProductTypeId' => 'required|exists:t_BudgetProducts,Id',
            'NoOfAccounts' => 'required|integer|min:1',
            'AllocationType' => 'required|in:full,monthly',
            'FullAllocation' => 'nullable|numeric|min:0|required_if:AllocationType,full',
            'monthly_allocations' => 'nullable|array|required_if:AllocationType,monthly',
            'monthly_allocations.*' => 'nullable|numeric|min:0',
        ]);
        DB::beginTransaction();

        try {
            $projection = BudgetProjection::create([
                'BudgetID' => $validated['BudgetID'],
                'BudgetLineID' => $validated['BudgetLineID'],
                'ProductID' => $validated['ProductTypeId'],
                'NumberOfAccounts' => $validated['NoOfAccounts'],
                'AllocationType' => $validated['AllocationType'],
                'FullAllocation' => $validated['AllocationType'] === 'full'
                    ? $validated['FullAllocation']
                    : 0,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            if ($validated['AllocationType'] === 'monthly') {
                for ($month = 1; $month <= 12; $month++) {
                    $amount = $validated['monthly_allocations'][$month] ?? 0.00;

                    BudgetProjectionData::create([
                        'BudgetProjectionID' => $projection->Id,
                        'ProductID' => $validated['ProductTypeId'],
                        'BudgetID' => $validated['BudgetID'],
                        'Amount' => $amount,
                        'Month' => $month,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                }
            }

            activity()
                ->performedOn($projection)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Budget Projections');

            DB::commit();

            return back()->with('success', 'Budget projection saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
            return back()->with('error', 'An error occurred while saving the projection.');
        }
    }


    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::BudgetSetupUpdate, BudgetProjection::class);

        $validated = $request->validate([
            'BudgetID' => 'required|exists:t_Budgets,Id',
            'BudgetLineID' => 'required|exists:t_BudgetLines,Id',
            'ProductTypeId' => 'required|exists:t_BudgetProducts,Id',
            'NoOfAccounts' => 'required|integer|min:1',
            'AllocationType' => 'required|in:full,monthly',
            'FullAllocation' => 'nullable|numeric|min:0|required_if:AllocationType,full',
            'monthly_allocations' => 'nullable|array|required_if:AllocationType,monthly',
            'monthly_allocations.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $projection = BudgetProjection::findOrFail($id);

            // Update projection fields
            $projection->update([
                'BudgetID' => $validated['BudgetID'],
                'BudgetLineID' => $validated['BudgetLineID'],
                'ProductID' => $validated['ProductTypeId'],
                'NumberOfAccounts' => $validated['NoOfAccounts'],
                'AllocationType' => $validated['AllocationType'],
                'FullAllocation' => $validated['AllocationType'] === 'full'
                    ? $validated['FullAllocation']
                    : 0,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Delete old allocations if they exist
            BudgetProjectionData::where('BudgetProjectionID', $projection->Id)->delete();

            // Re-insert if monthly
            if ($validated['AllocationType'] === 'monthly') {
                for ($month = 1; $month <= 12; $month++) {
                    $amount = $validated['monthly_allocations'][$month] ?? 0.00;

                    BudgetProjectionData::create([
                        'BudgetProjectionID' => $projection->Id,
                        'ProductID' => $validated['ProductTypeId'],
                        'BudgetID' => $validated['BudgetID'],
                        'Amount' => $amount,
                        'Month' => $month,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                }
            }

            activity()
                ->performedOn($projection)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Budget Projections');

            DB::commit();
            return redirect()->route('budgetprojections.index')->with('success', 'Budget projection updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while updating the projection: ' . $e->getMessage());
        }
    }


    public function show($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetMonthlyProjectionAllocation::class);

        $productsIDS = BudgetProjection::where('BudgetID', $id)->pluck('ProductID')->toArray();
        $products = [];
        foreach ($productsIDS as $productID) {
            $budgetProjectionID = BudgetProjection::where('BudgetID', $id)->where('ProductID', $productID)->pluck('Id')->first();
            $budgetProjection = BudgetProjection::find($budgetProjectionID);
            BudgetProduct::find($productID);
            //Getting PtoductType ID. This will be cleaned after we get actual data. SInce Id is being stores as string insteaf of foreignID
            $p_code = BudgetProduct::find($productID)->ProductTypeID;
            $productTypeID = BudgetProduct::where('ProductTypeID', $p_code)->pluck('Id')->first();

            $products[] = [
                'Name' => BudgetProduct::find($productID)->Description,
                'Volume' => BudgetProjection::where('BudgetID', $id)->where('ProductID', $productID)->sum('NumberOfAccounts'),
                'Id' => $budgetProjectionID,
                'Rate' => BudgetDriverRates::where('ProductTypeID', $productID)->pluck('RateValue')->first(),
                'Value' => $budgetProjection->AllocationType === 'full' ? $budgetProjection->FullAllocation : $budgetProjection->total_allocation ?? 0.00,
                'AllocationType' => $budgetProjection->AllocationType,
                'allocations' => $budgetProjection->allocations, //
            ];
        }

        //return $products;

        //$budget = BudgetDriverProjections::findOrFail($id);
        $budget = Budget::findOrFail($id);

        $rate = BudgetProduct::with(['rate'])
            ->get();
        // Fetch all allocations so the view can filter and display as needed
        $monthlyAllocations = BudgetProjectionData::where('BudgetID', $id)
            ->get();

        return view('budgetandanalytics.budgetworkspace.entry.show', compact('monthlyAllocations', 'budget', 'products'));
    }

    public function edit($id)
    {
        // Retrieve the projection to edit
        $projection = BudgetProjection::findOrFail($id);

        // Load all budgets and lines for dropdowns
        $budgets = Budget::all();
        $budgetLines = BudgetLine::whereHas('productTypes')->with('productTypes')->get();
        $products = BudgetProduct::all();

        // Get monthly allocations only if it's a monthly allocation
        $monthlyAllocations = collect(); // Default as empty collection
        if ($projection->AllocationType === 'monthly') {
            $monthlyAllocations = BudgetProjectionData::where('BudgetProjectionID', $projection->Id)->get()->keyBy('Month');
        }

        // You may want to get ProductTypeId (depending on how your dependent dropdown is handled)
        $productTypeId = null;
        $product = BudgetProduct::find($projection->ProductID);
        if ($product) {
            $productTypeId = $product->Id ?? null;
        }

        return view('budgetandanalytics.budgetworkspace.entry.edit', compact(
            'projection',
            'budgets',
            'budgetLines',
            'products',
            'monthlyAllocations',
            'productTypeId'
        ));
    }


    public function updateOld(Request $request, $id)
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
        } catch (Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['Error' => 'Failed to update: ' . $th->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetDriverProjections::class);
        DB::beginTransaction();
        try {
            //Delete All Projections for that Budget
            $budgetProjection = BudgetProjection::where('BudgetID', $id)->update(['DeletedBy' => Auth::id()]);
            $budgetProjectionData = $budgetProjection;
            BudgetProjection::where('BudgetID', $id)->delete();
            //Delete All allocation related to that Budget Id
            BudgetProjectionData::where('BudgetID', $id)->update(['DeletedBy' => Auth::id()]);
            BudgetProjectionData::where('BudgetID', $id)->delete();

            activity()
                ->performedOn(new BudgetProjection())
                ->causedBy(Auth::id())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted Budget Projections');

            DB::commit();
            return back()->with('success', 'Budget projections deleted successfully.');
        } catch (Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
            Log::error('Failed to delete budget projection: ' . $th->getMessage());
            return back()->withErrors('error', 'Failed to delete: ' . $th->getMessage());
        }
    }

    public function deleteProjection($id)
    {
        $this->authorize(PermissionEnum::BudgetSetupDelete, BudgetDriverProjections::class);

        try {
            DB::beginTransaction();
            $budgetProjection = BudgetProjection::find($id);
            $budgetProjectionData = $budgetProjection;
            //Delete allocations
            BudgetProjectionData::where('BudgetProjectionID', $id)->update(['DeletedBy' => Auth::id()]);;
            BudgetProjectionData::where('BudgetProjectionID', $id)->delete();
            //Delete Projection
            $budgetProjection->DeletedBy = Auth::id();
            $budgetProjection->delete();

            activity()
                ->performedOn($budgetProjectionData)
                ->causedBy(Auth::id())
                ->withProperties(['action' => 'delete'])
                ->log('Deleted Budget Projection');

            DB::commit();
            return back()->with('success', 'Budget projection deleted successfully.');
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Failed to delete budget projection: ' . $th->getMessage());
            return back()->with('error', 'Failed to delete: ' . $th->getMessage());
        }
    }
}
