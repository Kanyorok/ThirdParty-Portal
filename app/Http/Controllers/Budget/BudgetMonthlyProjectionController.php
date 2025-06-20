<?php
 
namespace App\Http\Controllers\Budget;
 
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Budget\BudgetMonthlyProjectionAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
 
class BudgetMonthlyProjectionController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::BudgetSetupView, BudgetMonthlyProjectionAllocation::class);
        // Fetch monthly allocations if needed, e.g., for displaying in the view
        $monthlyAllocations = BudgetMonthlyProjectionAllocation::all();
       
 
        return view('budgetandanalytics.budgetworkspace.monthly.index', compact('monthlyAllocations'));
    }
 
    public function create()
    {
        return view('budgetandanalytics.budgetworkspace.monthly.create');
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
 
            $monthlyAllocation=$request->MonthlyAllocations;
            if (!empty($monthlyAllocation)) {
                foreach ($monthlyAllocation as $key => $value) {
                    //Store each monthly allocation
                    $allocation = BudgetMonthlyProjectionAllocation::create([
                        'BudgetID' =>1,
                        'BudgetProjectionID' => 1,
                        'Month' => $key+1,
                        'Allocation' => $value,
                        'CreatedBy' => Auth::id(),
                        'ModifiedBy' => Auth::id(),
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('monthly.index')->with('success', 'Monthly projection allocation created successfully.');
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
 