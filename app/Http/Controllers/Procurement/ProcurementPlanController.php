<?php

namespace App\Http\Controllers\Procurement;

use App\enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemMasterList;
use App\Models\Procurement\ProcurementPeriod;
use App\Models\Procurement\ProcurementPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcurementPlanController extends Controller
{
    // {
    // }

    public function create(ProcurementPeriod $procurementPeriod)
    {
        $this->authorize('create', ProcurementPlan::class);
        $items = ItemMasterList::orderBy('ItemName')->get();

        return view('procurement.procurement_plans.create', [
            'availableItems' => $items,
            'period' => $procurementPeriod,
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
