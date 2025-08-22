<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fleet\FleetVehicle;

class FleetGpsController extends Controller
{
    // Simulated live tracking
    public function liveDashboard()
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();

        // Simulated location data for each vehicle
        $vehicleLocations = $vehicles->map(function ($vehicle) {
            return [
                'VehicleID' => $vehicle->VehicleID,
                'RegistrationNumber' => $vehicle->RegistrationNumber,
                'Latitude' => -1.28 + mt_rand(-50, 50) / 1000,  // Simulated around Nairobi
                'Longitude' => 36.82 + mt_rand(-50, 50) / 1000,
                'LastUpdated' => now()->subMinutes(rand(1, 30))->toDateTimeString(),
            ];
        });

        return view('fleet.gps.live_dashboard', compact('vehicleLocations'));
    }

    public function movementHistory(Request $request)
    {
        $vehicles = FleetVehicle::where('IsActive', 1)->get();

        $vehicleId = $request->input('vehicle_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $selectedVehicle = $vehicles->firstWhere('VehicleID', $vehicleId);
        $movementLogs = [];

        if ($vehicleId && $startDate && $endDate) {
            // Simulate 10 coordinate logs between selected dates
            $start = strtotime($startDate);
            $end = strtotime($endDate);
            $interval = ($end - $start) / 10;

            for ($i = 0; $i < 10; $i++) {
                $movementLogs[] = [
                    'Latitude' => -1.28 + mt_rand(-50, 50) / 1000,
                    'Longitude' => 36.82 + mt_rand(-50, 50) / 1000,
                    'Timestamp' => date('Y-m-d H:i:s', $start + ($i * $interval)),
                ];
            }
        }

        return view('fleet.gps.movement_history', compact(
            'vehicles',
            'vehicleId',
            'startDate',
            'endDate',
            'movementLogs',
            'selectedVehicle'
        ));
    }

}
