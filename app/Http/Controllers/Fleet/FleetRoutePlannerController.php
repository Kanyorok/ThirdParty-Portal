<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetRoutePlan;

class FleetRoutePlannerController extends Controller
{
    public function index()
    {
        $routes = FleetRoutePlan::with(['vehicle'])->orderByDesc('CreatedOn')->get();
        return view('fleet.route_planner.index', compact('routes'));
    }

    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        return view('fleet.route_planner.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'VehicleID'  => 'required|exists:t_FleetVehicles,Id',
            'TripName'   => 'required|string|max:255',
            'VehicleID'  => 'required|exists:t_FleetVehicles,Id',
            'Waypoints'  => 'required|array|min:2',
            'Waypoints.*'=> 'required|string|max:255',
        ]);

        FleetRoutePlan::create([
            'TripName'   => $validated['TripName'],
            'VehicleID'  => $validated['VehicleID'],
            'Waypoints'  => json_encode($validated['Waypoints']),
            'CreatedBy'  => Auth::id(),
            'CreatedOn'  => now(),
        ]);

        return redirect()->route('fleet.route_planner.index')->with('success', 'Route planned successfully.');
    }
}
