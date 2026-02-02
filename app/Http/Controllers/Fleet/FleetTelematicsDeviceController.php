<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\TelematicsDevice;
use Illuminate\Http\Request;

class FleetTelematicsDeviceController extends Controller
{
    public function index()
    {
        $devices = TelematicsDevice::with('vehicle')->get();

        return view('fleet.gps.telematics.index', compact('devices'));
    }

    public function create()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();

        return view('fleet.gps.telematics.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'VehicleID' => 'required|exists:t_FleetVehicles,VehicleID',
            'DeviceID' => 'required|string|max:100|unique:t_TelematicsDevices,DeviceID',
            'Provider' => 'required|string|max:100',
            'InstallDate' => 'required|date',
            'SubscriptionStatus' => 'required|string|max:20',
            'RenewalDate' => 'nullable|date',
            'TrackingURL' => 'nullable|url',
            'ApiKey' => 'nullable|string|max:255',
            'Notes' => 'nullable|string',
        ]);

        TelematicsDevice::create($request->all());

        return redirect()->route('fleet.telematics.index')->with('success', 'Device added successfully.');
    }
}
