<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\ContractedDriver;
use App\Models\Core\CodeDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetTripLogService
{
    /**
     * Generate a unique trip number
     */
  private function generateTripNo(): string
{    $latestTripNo = FleetTripLog::withTrashed()->latest('CreatedOn')->first();

    if (!$latestTripNo || !$latestTripNo->Id) {
        return 'TRP-0001';
    }

    $lastId = (int) str_replace('TRP-', '', $latestTripNo->Id);
    $newId = $lastId + 1;

    return 'TRP-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
}

    /**
     * Create a new trip
     */
    public function createTrip(array $data): FleetTripLog
    {
        return DB::transaction(function () use ($data) {

            $driver = $this->resolveDriver($data['DriverType'], $data['DriverID']);

            $tripLog = FleetTripLog::create([
                'TripNo' => $this->generateTripNo(),
                'VehicleID' => $data['VehicleID'],
                'DriverType' => $driver['type_id'],    // numeric FK
                'DriverID' => $driver['driver_id'],    // numeric FK
                'TripStartDate' => $data['TripStartDate'] ?? now(),
                'TripEndDate' => $data['TripEndDate'] ?? $data['TripStartDate'] ?? now(),
                'StartTime' => $data['StartTime'] ?? null,
                'EndTime' => $data['EndTime'] ?? null,
                'StartLocation' => $data['StartLocation'] ?? null,
                'EndLocation' => $data['EndLocation'] ?? null,
                'Route' => $data['Route'] ?? null,
                'DistanceCovered' => $data['DistanceCovered'] ?? null,
                'Purpose' => $data['Purpose'] ?? null,
                'Notes' => $data['Notes'] ?? null,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
            ]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($tripLog)
                ->log("Trip log created for VehicleID: {$tripLog->VehicleID}");

            return $tripLog;
        });
    }

    /**
     * Update an existing trip
     */
    public function updateTrip(FleetTripLog $tripLog, array $data): FleetTripLog
    {
        return DB::transaction(function () use ($tripLog, $data) {

            $driver = $this->resolveDriver($data['DriverType'], $data['DriverID']);

            $tripLog->update([
                'VehicleID' => $data['VehicleID'],
                'DriverType' => $driver['type_id'],   
                'DriverID' => $driver['driver_id'],   
                'TripStartDate' => $data['TripStartDate'] ?? $tripLog->TripStartDate,
                'TripEndDate' => $data['TripEndDate'] ?? $tripLog->TripEndDate,
                'StartTime' => $data['StartTime'] ?? $tripLog->StartTime,
                'EndTime' => $data['EndTime'] ?? $tripLog->EndTime,
                'StartLocation' => $data['StartLocation'] ?? $tripLog->StartLocation,
                'EndLocation' => $data['EndLocation'] ?? $tripLog->EndLocation,
                'Route' => $data['Route'] ?? $tripLog->Route,
                'DistanceCovered' => $data['DistanceCovered'] ?? $tripLog->DistanceCovered,
                'Purpose' => $data['Purpose'] ?? $tripLog->Purpose,
                'Notes' => $data['Notes'] ?? $tripLog->Notes,
            ]);

            $tripLog->ModifiedBy = Auth::id();
            $tripLog->ModifiedOn = now();
            $tripLog->save();

            $tripLog->delete();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($tripLog)
                ->log("Trip log updated for VehicleID: {$tripLog->VehicleID}");

            return $tripLog;
        });
    }

    /**
     * Delete a trip
     */
    public function deleteTrip(FleetTripLog $tripLog): void
    {
        DB::transaction(function () use ($tripLog) {

            $tripLog->DeletedBy = Auth::id();
            $tripLog->DeletedOn = now();
            $tripLog->save();
            activity()
            
                ->causedBy(Auth::user())
                ->performedOn($tripLog)
                ->log("Trip log deleted for VehicleID: {$tripLog->VehicleID}");

            $tripLog->delete();
        });
    }

    /**
     * Resolve driver type and ID for insertion
     */
    private function resolveDriver($driverTypeId, $driverId): array
    {
        $driverType = CodeDetail::findOrFail($driverTypeId);

        if ($driverType->Description === 'Permanent') {
            $driver = FleetDriver::where('Id', $driverId)->where('IsActive', 1)->firstOrFail();
        } elseif ($driverType->Description === 'Contracted') {
            $driver = ContractedDriver::where('Id', $driverId)->where('IsActive', 1)->firstOrFail();
        } else {
            throw new \Exception('Invalid Driver Type selected.');
        }

        return [
            'type_id' => $driverType->ID,   // pass numeric FK
            'driver_id' => $driver->Id      // numeric driver ID
        ];
    }
}
