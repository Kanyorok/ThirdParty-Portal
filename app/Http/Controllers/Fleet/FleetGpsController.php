<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\FleetVehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FleetGpsController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', FleetVehicle::class);
        if ($request->ajax()) {
            $vehicles = FleetVehicle::query()->get(['Id', 'RegistrationNo']);

            $vehicleLocations = $vehicles->map(function ($vehicle) {
                return [
                    'VehicleID' => $vehicle->Id,
                    'RegistrationNumber' => $vehicle->RegistrationNo,
                    'Latitude' => -1.28 + random_int(-5, 5) / 1000,
                    'Longitude' => 36.82 + random_int(-5, 5) / 1000,
                    'Speed' => random_int(0, 120),
                    'Direction' => random_int(0, 360),
                    'Status' => random_int(0, 1) ? 'Moving' : 'Idle',
                    'LastUpdated' => now()->toDateTimeString(),
                ];
            });

            return response()->json([
                'data' => $vehicleLocations,
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        return view('fleet.gps.index');
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
