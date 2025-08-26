<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetFuelLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetRunningCost;
use App\Models\Fleet\FuelType;

class FleetFuelLogController extends Controller
{
    // Show all fuel log entries
    public function index()
    {
        $fuelLogs = FleetFuelLog::with(['vehicle', 'trip'])->orderByDesc('LogDate')->get();
        return view('fleet.fuel_logs.index', compact('fuelLogs'));
    }

    // Show form to create a new fuel log
    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $trips = FleetTripLog::orderByDesc('TripDate')->get();
        $fuelTypes = FuelType::where('IsActive', 1)->orderBy('Name')->get();

        return view('fleet.fuel_logs.create', compact('vehicles', 'trips', 'fuelTypes'));
    }

    // Store the new fuel log
    public function store(Request $request)
    {
        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'TripID' => 'nullable|exists:t_TripLogs,TripID',
            'LogDate' => 'required|date',
            'OdometerStart' => 'nullable|integer|min:0',
            'OdometerEnd' => 'nullable|integer|min:0|gte:OdometerStart',
            'FuelAmount' => 'required|numeric|min:0.01',
            'FuelUnit' => 'required|string|max:10',
            'FuelTypeID' => 'required|exists:t_FuelTypes,ID',
            'Vendor' => 'nullable|string|max:255',
            'Notes' => 'nullable|string',
        ]);

        // Compute fuel efficiency if possible
        $efficiency = null;
        if (
            !empty($validated['OdometerStart']) &&
            !empty($validated['OdometerEnd']) &&
            !empty($validated['FuelAmount']) &&
            strtolower($validated['FuelUnit']) === 'litres'
        ) {
            $distance = $validated['OdometerEnd'] - $validated['OdometerStart'];
            $efficiency = $distance > 0 ? round($distance / $validated['FuelAmount'], 2) : null;
        }

        $fuelType = FuelType::find($validated['FuelTypeID']);
        // Save the fuel log
        $fuelLog = FleetFuelLog::create([
            ...$validated,
            'FuelType' => $fuelType->Name, // Add this line only if the column still exists
            'Efficiency' => $efficiency,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        // Add fuel cost to running costs if applicable (assuming unit cost logic is not yet implemented)
        if (!empty($validated['FuelAmount'])) {
            FleetRunningCost::create([
                'VehicleID' => $validated['VehicleID'],
                'CostType' => 'Fuel',
                'CostDate' => $validated['LogDate'],
                'Amount' => $validated['FuelAmount'], // Optionally: multiply by unit price if tracked
                'ReferenceSource' => 'FuelLog',
                'ReferenceID' => $fuelLog->id,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
            ]);
        }

        return redirect()->route('fleet.fuel_logs.index')->with('success', 'Fuel log recorded successfully.');
    }

}
