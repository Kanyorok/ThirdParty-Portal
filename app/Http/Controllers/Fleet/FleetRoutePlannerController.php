<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetRoutePlan;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FleetRoutePlannerController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', FleetRoutePlan::class);
        $routes = FleetRoutePlan::with(['vehicle'])->orderByDesc('CreatedOn')->get();

        return view('fleet.route_planner.index', compact('routes'));
    }

    public function create()
    {
        $vehicleStatuses = CodeDetail::where('CodeID', 'VehicleStatus')->get();
        $this->authorize('create', FleetRoutePlan::class);

        // Get active vehicles
        $activeStatusId = $vehicleStatuses->where('Description', 'Active')->first()->ID;
        $vehicles = FleetVehicle::where('Status', $activeStatusId)->get();

        // Get trips with their assigned vehicles
        $assignments = FleetVehicleAssignment::with(['vehicle', 'driver', 'trip.childTrips'])
            ->whereNotNull('TripNo')
            ->whereNotNull('VehicleID')
            ->whereHas('vehicle')
            ->whereHas('trip') // Ensure trip exists
            ->get();

        // Group by TripNo and get unique trips
        $trips = $assignments->groupBy('TripNo')->map(function ($assignments) {
            $assignment = $assignments->first();

            return [
                'assignment' => $assignment,
                'trip' => $assignment->trip,
                'vehicle' => $assignment->vehicle,
                'driver' => $assignment->driver,
                'has_children' => $assignment->trip->childTrips->isNotEmpty(),
                'child_trips' => $assignment->trip->childTrips,
            ];
        });

        return view('fleet.route_planner.create', compact('trips'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', FleetRoutePlan::class);
        $validated = $request->validate([
            'TripNo' => 'required|string|max:255',
            'VehicleID' => 'required|exists:t_FleetVehicles,Id',
            'Waypoints' => 'required|array|min:2',
            'Waypoints.*' => 'required|string|max:255',
        ]);

        FleetRoutePlan::create([
            'TripNo' => $validated['TripNo'],
            'VehicleID' => $validated['VehicleID'],
            'Waypoints' => json_encode($validated['Waypoints']),
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.route_planner.index')->with('success', 'Route planned successfully.');
    }

    // Optional: API endpoint to get child trips (for AJAX if needed)
    public function getChildTrips($tripNo)
    {
        $trip = FleetTripLog::where('TripNo', $tripNo)
            ->with('childTrips')
            ->first();

        if (! $trip) {
            return response()->json(['child_trips' => []]);
        }

        return response()->json([
            'child_trips' => $trip->childTrips->map(function ($child) {
                return [
                    'TripNo' => $child->TripNo,
                    'StartLocation' => $child->StartLocation,
                    'EndLocation' => $child->EndLocation,
                    'TripStartDate' => $child->TripStartDate,
                    'TripEndDate' => $child->TripEndDate,
                ];
            }),
        ]);
    }
}
