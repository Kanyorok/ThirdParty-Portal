<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetServiceAlert;
use App\Models\Fleet\FleetVehicle;

class FleetServiceAlertController extends Controller
{
    // View alerts
    public function index()
    {
        $alerts = FleetServiceAlert::with('vehicle')
            ->orderByDesc('TriggerDate')
            ->orderByDesc('TriggerMileage')
            ->get();

        return view('fleet.maintenance.alerts.index', compact('alerts'));
    }

    // Acknowledge alert
    public function acknowledge($id)
    {
        $alert = FleetServiceAlert::findOrFail($id);

        $alert->update([
            'IsAcknowledged' => true,
            'AcknowledgedBy' => Auth::id(),
            'AcknowledgedOn' => now(),
        ]);

        return redirect()->route('fleet.alerts.index')->with('success', 'Alert acknowledged.');
    }
}
