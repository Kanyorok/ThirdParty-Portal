<?php

namespace App\Http\Controllers\Fleet;

use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Fleet\FleetVehicle;
use App\Services\ThirdParty\iTrackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FleetGpsController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', FleetVehicle::class);
        if ($request->ajax()) {
            try {
                $service = new iTrackService();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to connect to iTrack'], 400);
            }

            $vehicles = FleetVehicle::query()->whereNotNull('TrackerNo')->get(['Id', 'RegistrationNo', 'TrackerNo']);
            try {
                $locations = $service->findMultipleTrack($vehicles->pluck('TrackerNo')->toArray());
            } catch (ErroredException $e) {
                return response()->json(['error' => 'Failed to fetch GPS data'], 400);
            }
            $vehicleLocations = collect();
            $locations->each(function ($location) use ($vehicleLocations, $vehicles) {
                $vehicle = $vehicles->firstWhere('TrackerNo', $location['imei']);
                if ($vehicle) {
                    if ($location['accstatus'] === 0) {
                        $status = 'Idle';
                    } elseif ($location['speed'] > 0) {
                        $status = 'Moving';
                    } else {
                        $status = 'Stopped';
                    }
                    $vehicleLocations->add([
                        'VehicleID' => $vehicle->Id,
                        'RegistrationNumber' => $vehicle->RegistrationNo,
                        'Latitude' => $location['latitude'],
                        'Longitude' => $location['longitude'],
                        'Speed' => $location['speed'],
                        'Direction' => $location['course'],
                        'Status' => $status,
                        'LastUpdated' => now()->timestamp($location['gpstime'])->toDateTimeString()
                    ]);

                }
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
