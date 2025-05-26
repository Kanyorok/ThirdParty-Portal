<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\BudgetMaster;
use App\Models\Procurement\PlanLineItems;
use App\Models\HRM\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Procurement\BudgetLineLink;


class MapToBudgetController extends Controller
{

        public function index()
    {
        $draftItems = PlanLineItems::whereHas('consolidatedProcurementPlan', function ($query) {
        $query->where('Status', 'd');
    })->get();
        
        $budgetLines = BudgetMaster::all();

        return view('procurement.procurementplan.planneditemsandactivities.linktobudget.index', compact('draftItems', 'budgetLines'));
    }
    public function store(Request $request)
{
    $userId = Auth::id(); // get current user ID

    foreach ($request->lineItemIds as $lineItemId) {
        $budgetLineId = $request->input("budgetLine_$lineItemId");

       if ($budgetLineId) {
            // Fetch item to calculate amount
            $item = PlanLineItems::find($lineItemId);
            if (!$item) continue;

            $amount = $item->MergedQty * $item->EstimatedUnitCost;
            BudgetLineLink::create([
                'LineItemID'     => $lineItemId,
                'BudgetLineID'   => $budgetLineId,
                'AmountAllocated'=>$amount,
                'LinkedBy'       => $userId,
                'LinkedDate'     => Carbon::now(),
                'CreatedBy'      => $userId,
                'ModifiedBy'     => $userId,
            ]);
        }
    }

    return redirect()->back()->with('success', 'Budget lines linked successfully.');
}

    public function create(){
        return view('procurement.procurementplan.planneditemsandactivities.linktobudget.create');
    }

}
