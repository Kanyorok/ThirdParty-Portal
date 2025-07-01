<?php

namespace App\Http\Controllers\Budget;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetDriverProjections;
use App\Models\Budget\BudgetMonthlyProjectionAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetMonthlyProjectionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetMonthlyProjectionAllocation::class);
        // Only show allocations for a specific projection if 'id' is provided
        $projectionID = $request->query('id');
        if ($projectionID) {
            $monthlyAllocations = BudgetMonthlyProjectionAllocation::where('BudgetProjectionID', $projectionID)->get();
        } else {
            $monthlyAllocations = collect(); // Return empty if no projection specified
        }
        return view('budgetandanalytics.budgetworkspace.monthly.index', compact('monthlyAllocations', 'projectionID'));
    }

    public function create(Request $request)
    {
        $projectionID = $request->query('id');
        return view('budgetandanalytics.budgetworkspace.monthly.create', compact('projectionID'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetMonthlyProjectionAllocation::class);

        // $validated = $request->validate([
        //     'BudgetID' => 'required|exists:t_Budgets,Id',
        //     'BudgetProjectionID' => 'required',
        // ]);

        DB::beginTransaction();
        try {

            $projecionId = $request->projectionID;
            //fetch BudgetId
            $budgetId = BudgetDriverProjections::where('Id', $projecionId)->value('BudgetID');


            $monthlyAllocation = $request->MonthlyAllocations;
            if (!empty($monthlyAllocation)) {
                foreach ($monthlyAllocation as $key => $value) {
                    //Store each monthly allocation
                    $allocation = BudgetMonthlyProjectionAllocation::create([
                        'BudgetID' => $budgetId,
                        'BudgetProjectionID' => $projecionId,
                        'Month' => $key + 1,
                        'Allocation' => $value,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('budgetprojections.index')->with('success', 'Monthly projection allocation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            activity()
                ->performedOn(new BudgetMonthlyProjectionAllocation())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Failed to create monthly projection allocation: ' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'Failed to create monthly projection allocation: ' . $e->getMessage()]);
        }
    }

}
