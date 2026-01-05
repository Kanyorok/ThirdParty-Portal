<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Procurement\ProcurementPlan;
use App\Models\Procurement\ProcurementPeriod;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory\ItemMasterList;
use Illuminate\Http\Request;
use App\enums\ProcurementPlanStatusEnum;

class ProcurementPlanController extends Controller
{
    // public function index()
    // {
    //     $procurementPlans = ProcurementPlan::with(['item', 'procurementPeriod'])->get();
    //     return view('procurement.procurement_plans.index', compact('procurementPlans'));
    // }

    public function create(ProcurementPeriod $procurementPeriod)
    {
        $this->authorize('create', ProcurementPlan::class);
        $items = ItemMasterList::orderBy('ItemName')->get();
        return view('procurement.procurement_plans.create', [
            'availableItems' => $items,
            'period' =>  $procurementPeriod,
        ]); 
    }

    public function store(Request $request, ProcurementPeriod $procurementPeriod) // Route Model Binding
    {
        $this->authorize('create', ProcurementPlan::class);
        $validated = $request->validate([
            'ItemId' => 'required|exists:t_Items,Id',
            'Quantity' => 'required|numeric|min:0.01',
            'Category' => 'nullable|string|max:100',
            'UOM' => 'nullable|string|max:50',
            'PlannedQuarter' => 'nullable|string|max:10',
            'ExpectedDeliveryDate' => 'nullable|date',
        ]);

        $item = ItemMasterList::findOrFail($validated['ItemId']);

        $totalCost = ($item->UnitPrice ?? 0) * $validated['Quantity'];

        ProcurementPlan::create([
            'ProcurementPeriodId' => $procurementPeriod->Id,
            'ItemId' => $validated['ItemId'],
            'Quantity' => $validated['Quantity'],
            'Category' => $validated['Category'] ?? $item->Category ?? null,
            'UOM' => $validated['UOM'] ?? $item->UOM ?? null,
            'PlannedQuarter' => $validated['PlannedQuarter'],
            'ExpectedDeliveryDate' => $validated['ExpectedDeliveryDate'],
            'Status' => ProcurementPlanStatusEnum::Draft,
            'TotalCost' => $totalCost,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
        ]);
        return redirect()->route('procurement-periods.show', $procurementPeriod->Id)
            ->with('success', 'Procurement plan item added successfully.');
    }
}
