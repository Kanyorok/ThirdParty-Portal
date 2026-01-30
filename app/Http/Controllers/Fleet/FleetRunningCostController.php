<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\FleetRunningCost;
use App\Models\Fleet\FleetVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FleetRunningCostController extends Controller
{
    // Show list of all running costs
    public function index()
    {
        $costs = FleetRunningCost::with('vehicle')->orderByDesc('CostDate')->get();

        return view('fleet.running_costs.index', compact('costs'));
    }

    // Show form to create a new running cost entry
    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();

        return view('fleet.running_costs.create', compact('vehicles'));
    }

    // Store a new running cost entry
    public function store(Request $request)
    {
        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'CostType' => 'required|string|max:100',
            'CostDate' => 'required|date',
            'Amount' => 'required|numeric|min:0',
            'Vendor' => 'nullable|string|max:255',
            'Notes' => 'nullable|string',
        ]);

        FleetRunningCost::create([
            ...$validated,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.running_costs.index')->with('success', 'Running cost logged successfully.');
    }
}
