<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PostingEnum;
use App\Http\Controllers\Controller;
use App\Models\Procurement\BudgetLineLink;
use App\Models\Procurement\BudgetMaster;
use App\Models\Procurement\PlanLineItems;
use Illuminate\Http\Request;
use Illuminate\View\View;


class MapToBudgetController extends Controller
{

        public function index()
    {
        $draftItems = PlanLineItems::whereHas('consolidatedProcurementPlan', static function ($query) {
            $query->where('Status', PostingEnum::Draft);
        })->get();

        $budgetLines = BudgetMaster::all();

        return view('procurement.procurementplan.planneditemsandactivities.linktobudget.index', compact('draftItems', 'budgetLines'));
    }
    public function store(Request $request)
{
    $userId = $request->user()->id;

    foreach ($request->lineItemIds as $lineItemId) {
        $budgetLineId = $request->input("budgetLine_$lineItemId");
//todo validate this data
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
                'LinkedDate' => now(),
                'CreatedBy'      => $userId,
                'ModifiedBy'     => $userId,
            ]);
        }
    }

    return redirect()->back()->with('success', 'Budget lines linked successfully.');
}

    public function create(): View //todo why
    {
        return view('procurement.procurementplan.planneditemsandactivities.linktobudget.create');
    }

}
