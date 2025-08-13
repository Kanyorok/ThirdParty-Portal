<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\ContractedDriver;

class FleetTripLogController extends Controller
{
public function index()
{
    $tripLogs = FleetTripLog::with(['vehicle', 'creator'])->orderByDesc('TripDate')->get();
    return view('fleet.trip_logs.index', compact('tripLogs'));
}

    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();
        $drivers = FleetDriver::where('IsActive', 1)->get();
        $contractedDrivers = ContractedDriver::where('IsActive', 1)->get();

        return view('fleet.trip_logs.create', compact('vehicles', 'drivers', 'contractedDrivers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'DriverType' => 'required|in:Permanent,Contracted',
            'DriverID' => 'required|integer',
            'TripDate' => 'required|date',
            'StartTime' => 'nullable|date_format:H:i',
            'EndTime' => 'nullable|date_format:H:i|after_or_equal:StartTime',
            'StartLocation' => 'nullable|string|max:255',
            'EndLocation' => 'nullable|string|max:255',
            'DistanceCovered' => 'nullable|numeric|min:0',
            'Purpose' => 'nullable|string|max:255',
            'Notes' => 'nullable|string',
        ]);

        FleetTripLog::create([
            ...$validated,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('fleet.trip_logs.index')->with('success', 'Trip logged successfully.');
    }
}
