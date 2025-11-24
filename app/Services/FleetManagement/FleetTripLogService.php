<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetTripLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Core\Workflow;
use App\Models\Core\PendingWorkflow;
use App\Enums\WorkflowStatus;
use App\Models\Core\Approval\CodeDetail;


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

        $lastNumber = (int)str_replace('TRP-', '', $latestTrip->TripNo);
        $newNumber = $lastNumber + 1;

        return 'TRP-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new parent trip.
     */
    public function createParentTrip(array $data): FleetTripLog
    {
        return DB::transaction(function () use ($data) {
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

    /**
     * Approve a parent trip and all its child trips
     */
    public function approveTrip(int $tripId): FleetTripLog
    {
        return DB::transaction(function () use ($tripId) {
            $parentTrip = FleetTripLog::with('childTrips')->findOrFail($tripId);
            
            $approvedStatusId = $this->getTripStatusId('Approved');
            
            $parentTrip->update([
                'ApprovedOn' => now(),
                'ApprovedBy' => Auth::id(),
                'Status' => $approvedStatusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            if ($parentTrip->childTrips->isNotEmpty()) {
                FleetTripLog::where('ParentTripID', $parentTrip->Id)
                    ->update([
                        'ApprovedOn' => now(),
                        'ApprovedBy' => Auth::id(),
                        'Status' => $approvedStatusId,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
            }

            // Log workflow for parent
            $this->logTripWorkflow($parentTrip->Id, $approvedStatusId, 'Trip approved');

            // Log workflows for child trips
            foreach ($parentTrip->childTrips as $childTrip) {
                $this->logTripWorkflow($childTrip->Id, $approvedStatusId, 'Child trip approved');
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($parentTrip)
                ->event('approved')
                ->log("Trip {$parentTrip->TripNo} and all child trips approved");

            return $parentTrip->fresh(['childTrips']);
        });
    }

    /**
     * Reject a parent trip and all its child trips
     */
    public function rejectTrip(int $tripId): FleetTripLog
    {
        return DB::transaction(function () use ($tripId) {
            $parentTrip = FleetTripLog::with('childTrips')->findOrFail($tripId);
            $rejectedStatusId = $this->getTripStatusId('Rejected');
            $parentTrip->update([
                'ApprovedOn' => now(),
                'ApprovedBy' => Auth::id(),
                'Status' => $rejectedStatusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Update all child trips status
            if ($parentTrip->childTrips->isNotEmpty()) {
                FleetTripLog::where('ParentTripID', $parentTrip->Id)
                    ->update([
                        'ApprovedOn' => now(),
                        'ApprovedBy' => Auth::id(),
                        'Status' => $rejectedStatusId,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
            }

            // Log workflow for parent
            $this->logTripWorkflow($parentTrip->Id, $rejectedStatusId, 'Trip rejected');

            // Log workflows for child trips
            foreach ($parentTrip->childTrips as $childTrip) {
                $this->logTripWorkflow($childTrip->Id, $rejectedStatusId, 'Child trip rejected');
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($parentTrip)
                ->event('rejected')
                ->log("Trip {$parentTrip->TripNo} and all child trips rejected");

            return $parentTrip->fresh(['childTrips']);
        });
    }

    /**
     * Get trip status ID by description
     */
    private function getTripStatusId(string $description): int
    {
        $status = CodeDetail::where('CodeID', 'TripStatus')
            ->where('Description', $description)
            ->first();

        if (!$status) {
            throw new \Exception("Trip status '{$description}' not found in system configuration.");
        }

        // Support different column names (ID / Id / id)
        return $status->ID ?? $status->Id ?? $status->id;
    }

    /**
     * Log workflow for trip approval/rejection
     */
    private function logTripWorkflow(int $tripId, int $statusId, string $notes = null): void
    {
        // Get CodeDetail for the status id
        $code = CodeDetail::where('CodeID', 'TripStatus')->find($statusId);

        // Prefer Value, then CodeValue, then Description
        $statusValue = $code->Value ?? $code->Value ?? $code->Description ?? null;

        // Save into t_Workflow: Stage holds CodeDetail ID, Status holds the CodeDetail value/label
        Workflow::create([
            'Source' => 'TripLog',
            'SourceID' => $tripId,
            'Stage' => $statusId,        // CodeDetail ID
            'Status' => $statusValue,    // CodeDetail value (or description)
            'Notes' => $notes,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        // Ensure pending workflow stores the CodeDetail value too
        PendingWorkflow::updateOrCreate(
            ['Source' => 'TripLog', 'SourceID' => $tripId],
            [
                'Stage' => $statusId,
                'Status' => $statusValue,
                'UserId' => Auth::id(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]
        );
    }
}
