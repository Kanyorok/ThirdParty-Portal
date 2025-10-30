<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetTripLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetTripLogService
{
    /**
     * Generate a unique trip number
     */
    private function generateTripNo(): string
    {
        $latestTrip = FleetTripLog::withTrashed()->latest('CreatedOn')->first();

        if (!$latestTrip || !$latestTrip->TripNo) {
            return 'TRP-0001';
        }

        $lastNumber = (int) str_replace('TRP-', '', $latestTrip->TripNo);
        $newNumber = $lastNumber + 1;

        return 'TRP-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new parent trip.
     */
    public function createParentTrip(array $data): FleetTripLog
    {
        return DB::transaction(function () use ($data) {
            // ✅ Using Description field for lookup
            $scheduledStatus = \App\Models\Core\CodeDetail::where('CodeID', 'TripStatus')
                ->where('Description', 'Scheduled')
                ->value('ID');

            if (!$scheduledStatus) {
                $scheduledStatus = \App\Models\Core\CodeDetail::where('CodeID', 'TripStatus')
                    ->orderBy('Description')
                    ->value('ID');
                
                if (!$scheduledStatus) {
                    \Log::warning('No TripStatus found in CodeDetail table');
                    throw new \Exception('Scheduled status not found in system configuration.');
                }
            }

            $tripLog = FleetTripLog::create([
                'TripNo'           => $this->generateTripNo(),
                'ParentTripID'     => null,
                'TripType'         => $data['TripType'],
                'TripCode'         => $data['TripCode'] ?? null,
                'VehicleType'      => $data['VehicleType'],
                'LoadType'         => $data['LoadType'] ?? null,
                'TripStartDate'    => $data['TripStartDate'] ?? now(),
                'TripEndDate'      => $data['TripEndDate'] ?? $data['TripStartDate'] ?? now(),
                'StartTime'        => $data['StartTime'] ?? null,
                'EndTime'          => $data['EndTime'] ?? null,
                'StartLocation'    => $data['StartLocation'] ?? null,
                'EndLocation'      => $data['EndLocation'] ?? null,
                'Route'            => $data['Route'] ?? null,
                'DistanceCovered'  => $data['DistanceCovered'] ?? null,
                'Purpose'          => $data['Purpose'] ?? null,
                'Notes'            => $data['Notes'] ?? null,
                'Status'           => $scheduledStatus,
                'CreatedBy'        => Auth::id(),
                'CreatedOn'        => now(),
            ]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($tripLog)
                ->event('created')
                ->log("Created parent trip {$tripLog->TripNo} with status Scheduled");

            return $tripLog;
        });
    }

    /**
     * Create child trips for a parent trip.
     */
    public function createChildTrips(FleetTripLog $parent, array $childData): void
    {
        DB::transaction(function () use ($parent, $childData) {
            // ✅ Using Description field for lookup
            $scheduledStatus = \App\Models\Core\CodeDetail::where('CodeID', 'TripStatus')
                ->where('Description', 'Scheduled')
                ->value('ID');

            if (!$scheduledStatus) {
                $scheduledStatus = \App\Models\Core\CodeDetail::where('CodeID', 'TripStatus')
                    ->orderBy('Description')
                    ->value('ID');
            }

            if (!empty($childData) && is_array($childData)) {
                foreach ($childData as $child) {
                    FleetTripLog::create([
                        'TripNo' => $this->generateTripNo(),
                        'ParentTripID' => $parent->Id,
                        'TripType' => $parent->TripType,
                        'TripCode' => $parent->TripCode,
                        'VehicleType' => $parent->VehicleType,
                        'LoadType' => $child['LoadType'] ?? $parent->LoadType,
                        'TripStartDate' => $child['TripStartDate'] ?? null,
                        'TripEndDate' => $child['TripEndDate'] ?? null,
                        'StartTime' => $child['StartTime'] ?? null,
                        'EndTime' => $child['EndTime'] ?? null,
                        'StartLocation' => $child['StartLocation'] ?? null,
                        'EndLocation' => $child['EndLocation'] ?? null,
                        'DistanceCovered' => $child['DistanceCovered'] ?? null,
                        'Route' => $child['Route'] ?? null,
                        'Purpose' => $child['Purpose'] ?? null,
                        'Notes' => $child['Notes'] ?? null,
                        'Status' => $scheduledStatus,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                    ]);
                }

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($parent)
                    ->event('created')
                    ->log("Created " . count($childData) . " child trip(s) under parent {$parent->TripNo} with status Scheduled");
            }
        });
    }

    /**
     * Update an existing trip
     */
    public function updateTrip(FleetTripLog $tripLog, array $data): FleetTripLog
    {
        return DB::transaction(function () use ($tripLog, $data) {
            $tripLog->update([
                'TripType' => $data['TripType'],
                'VehicleType' => $data['VehicleType'], 
                'LoadType' => $data['LoadType'], 
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
            ]);

            $tripLog->ModifiedBy = Auth::id();
            $tripLog->ModifiedOn = now();
            $tripLog->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($tripLog)
                ->log("Trip log updated");

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
                ->log("Trip log deleted");

            $tripLog->delete();
        });
    }
}