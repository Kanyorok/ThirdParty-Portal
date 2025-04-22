<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\ProcurementPlan;
use App\Models\Procurement\ProcurementPeriod;
use Illuminate\Support\Facades\Auth;
use App\Models\Procurement\Item;
use Illuminate\Http\Request;

class ProcurementPlanController extends Controller
{
    // public function index()
    // {
    //     $procurementPlans = ProcurementPlan::with(['item', 'procurementPeriod'])->get();
    //     return view('procurement.procurement_plans.index', compact('procurementPlans'));
    // }

    public function create($procurementPeriodId)
    {
        $period = ProcurementPeriod::findOrFail($procurementPeriodId);
        $items = Item::all();
        return view('procurement.procurement_plans.create', compact('items', 'period'));
    }

    public function store(Request $request, $period)
    {
        $validated = $request->validate([
            'ItemId' => 'required|exists:t_Items,Id',
            'Quantity' => 'required|integer|min:1',
        ]);

        $item = Item::findOrFail($validated['ItemId']);
    
        $totalCost = $item->UnitPrice * $validated['Quantity'];

        ProcurementPlan::create([
            'ProcurementPeriodId' => $period,
            'ItemId' => $validated['ItemId'],
            'Quantity' => $validated['Quantity'],
            'TotalCost' => $totalCost,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
        ]);
    
        return redirect()->route('procurement-periods.show', $period)
            ->with('success', 'Procurement Plan Added');
    }
}
