<?php

namespace App\Services\FleetManagement;

use App\Enums\Core\ApprovalEnum;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\PendingWorkflow;
use App\Models\Core\Workflow;
use App\Models\Fleet\FleetTripLog;
use App\Services\Workflow\ApprovalWorkflow;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FleetTripLogService
{
    protected ApprovalWorkflow $workflow;

    public function __construct(ApprovalWorkflow $workflow)
    {
        $this->workflow = new ApprovalWorkflow('TripStatus', 'Status');
    }

    /**
     * Generate a unique trip number
     */
    private function generateTripNo(): string
    {
        $latestTrip = FleetTripLog::withTrashed()->latest('CreatedOn')->first();

        if (! $latestTrip || ! $latestTrip->TripNo) {
            return 'TRP-0001';
        }

        $lastNumber = (int)str_replace('TRP-', '', $latestTrip->TripNo);
        $newNumber = $lastNumber + 1;

        return 'TRP-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new parent trip with workflow submission
     */
    public function createParentTrip(array $data): FleetTripLog
    {
        DB::beginTransaction();

        try {
            $scheduledStatus = $this->getTripStatusId('Scheduled');

            $tripLog = FleetTripLog::create([
                'TripNo' => $this->generateTripNo(),
                'ParentTripID' => null,
                'TripType' => $data['TripType'],
                'TripCode' => $data['TripCode'] ?? null,
                'VehicleType' => $data['VehicleType'],
                'LoadType' => $data['LoadType'] ?? null,
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
                'Status' => $scheduledStatus,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Log::info('Parent trip created successfully', [
                'trip_id' => $tripLog->Id,
                'trip_no' => $tripLog->TripNo,
                'status' => $tripLog->Status,
            ]);

            // Submit for approval workflow
            $this->workflow->submit(
                $tripLog,
                Auth::user(),
                ApprovalEnum::Scheduled,
                'Fleet Trip Scheduled and Submitted for Approval'
            );

            Log::info('Workflow submitted for trip', [
                'trip_id' => $tripLog->Id,
                'user_id' => Auth::id(),
                'status' => $tripLog->Status,
            ]);

            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($tripLog)
                ->event('created')
                ->withProperties(['attributes' => $tripLog->toArray()])
                ->log("Created parent trip {$tripLog->TripNo} and submitted for approval");

            return $tripLog;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create parent trip', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Create child trips with workflow submission
     */
    public function createChildTrips(FleetTripLog $parent, array $childData): void
    {
        Log::info('Creating child trips', [
            'parent_trip_id' => $parent->Id,
            'child_count' => count($childData),
            'user_id' => Auth::id(),
        ]);

        DB::beginTransaction();

        try {
            $scheduledStatus = $this->getTripStatusId('Scheduled');

            foreach ($childData as $child) {
                $childTrip = FleetTripLog::create([
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
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Submit child trip for approval workflow
                $this->workflow->submit(
                    $childTrip,
                    Auth::user(),
                    ApprovalEnum::Scheduled,
                    'Child Trip Created and Submitted for Approval'
                );

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($childTrip)
                    ->withProperties(['attributes' => $childTrip->toArray()])
                    ->log('Created child trip and submitted for approval');
            }

            DB::commit();

            Log::info('Child trips created successfully', [
                'parent_trip_id' => $parent->Id,
                'child_count' => count($childData),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create child trips', [
                'parent_trip_id' => $parent->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Approve a trip and all its child trips
     */
    public function approveTrip(int $tripId, string $comments = null): FleetTripLog
    {
        Log::info('=== FleetTripLogService::approveTrip START ===', [
            'trip_id' => $tripId,
            'user_id' => Auth::id(),
            'comments' => $comments,
        ]);

        DB::beginTransaction();

        try {
            $parentTrip = FleetTripLog::with('childTrips')->findOrFail($tripId);
            $user = Auth::user();

            Log::info('Found trip for approval', [
                'trip_id' => $parentTrip->Id,
                'trip_no' => $parentTrip->TripNo,
                'current_status' => $parentTrip->Status,
                'child_count' => $parentTrip->childTrips->count(),
            ]);

            // Use workflow to approve parent trip
            $this->workflow->approve(
                $parentTrip,
                $user,
                ApprovalEnum::Approved,
                $comments ?? 'Trip Approved',
                'Status'
            );

            // Update parent trip status
            $approvedStatusId = $this->getTripStatusId('Approved');
            $parentTrip->update([
                'ApprovedOn' => now(),
                'ApprovedBy' => Auth::id(),
                'Status' => $approvedStatusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Log::info('Parent trip approved', [
                'trip_id' => $parentTrip->Id,
                'new_status' => $parentTrip->Status,
            ]);

            // Approve all child trips
            if ($parentTrip->childTrips->isNotEmpty()) {
                foreach ($parentTrip->childTrips as $childTrip) {
                    // Use workflow to approve child trip


                    $this->workflow->approve($childTrip, $user, ApprovalEnum::Approved, 'Child Trip Approved with Parent', 'Status');

                    $childTrip->update([
                        'ApprovedOn' => now(),
                        'ApprovedBy' => Auth::id(),
                        'Status' => $approvedStatusId,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);

                    activity()
                        ->causedBy(Auth::user())
                        ->performedOn($childTrip)
                        ->withProperties(['attributes' => $childTrip->toArray()])
                        ->log('Approved child trip');
                }

                Log::info('Child trips approved', [
                    'parent_trip_id' => $parentTrip->Id,
                    'child_count' => $parentTrip->childTrips->count(),
                ]);
            }

            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($parentTrip)
                ->event('approved')
                ->withProperties(['attributes' => $parentTrip->toArray()])
                ->log("Approved trip {$parentTrip->TripNo} and all child trips");

            Log::info('=== FleetTripLogService::approveTrip SUCCESS ===', [
                'trip_id' => $parentTrip->Id,
                'user_id' => Auth::id(),
            ]);

            return $parentTrip->fresh(['childTrips']);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('=== FleetTripLogService::approveTrip FAILED ===', [
                'trip_id' => $tripId,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'exception' => $th,
                'trace' => $th->getTraceAsString(),
            ]);

            throw $th;
        }
    }

    /**
     * Reject a trip and all its child trips
     */
    public function rejectTrip(int $tripId, string $comments = null): FleetTripLog
    {
        Log::info('=== FleetTripLogService::rejectTrip START ===', [
            'trip_id' => $tripId,
            'user_id' => Auth::id(),
            'comments' => $comments,
        ]);

        DB::beginTransaction();

        try {
            $parentTrip = FleetTripLog::with('childTrips')->findOrFail($tripId);
            $user = Auth::user();

            Log::info('Found trip for rejection', [
                'trip_id' => $parentTrip->Id,
                'trip_no' => $parentTrip->TripNo,
                'current_status' => $parentTrip->Status,
            ]);

            // Use workflow to reject parent trip
            $this->workflow->reject(
                $parentTrip,
                $user,
                ApprovalEnum::Rejected,
                $comments ?? 'Trip Rejected',
                'Status'
            );

            // Update parent trip status
            $rejectedStatusId = $this->getTripStatusId('Rejected');
            $parentTrip->update([
                'Status' => $rejectedStatusId,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Reject all child trips
            if ($parentTrip->childTrips->isNotEmpty()) {
                foreach ($parentTrip->childTrips as $childTrip) {
                    // Use workflow to reject child trip
                    $this->workflow->reject($childTrip, $user, TripStatus::Rejected, 'Child Trip Rejected with Parent', 'Status');

                    $childTrip->update([
                        'Status' => $rejectedStatusId,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                }
            }

            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($parentTrip)
                ->event('rejected')
                ->withProperties(['attributes' => $parentTrip->toArray()])
                ->log("Rejected trip {$parentTrip->TripNo} and all child trips");

            Log::info('=== FleetTripLogService::rejectTrip SUCCESS ===', [
                'trip_id' => $parentTrip->Id,
                'new_status' => $parentTrip->Status,
            ]);

            return $parentTrip->fresh(['childTrips']);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('=== FleetTripLogService::rejectTrip FAILED ===', [
                'trip_id' => $tripId,
                'user_id' => Auth::id(),
                'error' => $th->getMessage(),
                'exception' => $th,
                'trace' => $th->getTraceAsString(),
            ]);

            throw $th;
        }
    }

    /**
     * Update an existing trip with workflow tracking
     */
    public function updateTrip(FleetTripLog $tripLog, array $data): FleetTripLog
    {
        Log::info('Updating trip', [
            'trip_id' => $tripLog->Id,
            'data_keys' => array_keys($data),
            'user_id' => Auth::id(),
        ]);

        return DB::transaction(function () use ($tripLog, $data) {
            $tripLog->update([
                'TripType' => $data['TripType'],
                'VehicleType' => $data['VehicleType'],
                'LoadType' => $data['LoadType'],
                'TripStartDate' => $data['TripStartDate'] ?? $tripLog->TripStartDate,
                'TripEndDate' => $data['TripEndDate'] ?? $data['TripStartDate'] ?? $tripLog->TripEndDate,
                'StartTime' => $data['StartTime'] ?? $tripLog->StartTime,
                'EndTime' => $data['EndTime'] ?? $tripLog->EndTime,
                'StartLocation' => $data['StartLocation'] ?? $tripLog->StartLocation,
                'EndLocation' => $data['EndLocation'] ?? $tripLog->EndLocation,
                'Route' => $data['Route'] ?? $tripLog->Route,
                'DistanceCovered' => $data['DistanceCovered'] ?? $tripLog->DistanceCovered,
                'Purpose' => $data['Purpose'] ?? $tripLog->Purpose,
                'Notes' => $data['Notes'] ?? $tripLog->Notes,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            if (! empty($data['childTrips'])) {
                foreach ($data['childTrips'] as $childId => $childData) {
                    if (is_numeric($childId)) {
                        $child = FleetTripLog::find($childId);
                        if ($child) {
                            $child->update($childData);
                        }
                    }
                }
            }

            // Log workflow for trip update
            $this->workflow->update(
                $tripLog,
                Auth::user(),
                ApprovalEnum::from($tripLog->Status),
                'Trip Updated'
            );

            activity()
                ->causedBy(Auth::user())
                ->performedOn($tripLog)
                ->withProperties(['attributes' => $data])
                ->log("Updated trip log {$tripLog->TripNo}");

            return $tripLog->fresh();
        });
    }

    /**
     * Delete a trip with workflow cleanup
     */
    public function deleteTrip(FleetTripLog $tripLog): void
    {
        Log::info('Deleting trip', [
            'trip_id' => $tripLog->Id,
            'trip_no' => $tripLog->TripNo,
            'user_id' => Auth::id(),
        ]);

        DB::transaction(function () use ($tripLog) {
            // Clean up workflow records
            Workflow::where('Source', 'TripLog')
                ->where('SourceID', $tripLog->Id)
                ->delete();

            PendingWorkflow::where('Source', 'TripLog')
                ->where('SourceID', $tripLog->Id)
                ->delete();

            // Delete child trips if any
            if ($tripLog->childTrips->isNotEmpty()) {
                FleetTripLog::where('ParentTripID', $tripLog->Id)->delete();
            }

            $tripLog->DeletedBy = Auth::id();
            $tripLog->DeletedOn = now();
            $tripLog->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($tripLog)
                ->log("Trip log {$tripLog->TripNo} deleted");

            $tripLog->delete();
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

        if (! $status) {
            throw new Exception("Trip status '{$description}' not found in system configuration.");
        }

        return $status->ID ?? $status->Id ?? $status->id;
    }

    /**
     * Get approved trips for selection (e.g., for linking to other entities)
     */
    public function getApprovedTrips()
    {
        $approvedStatusId = $this->getTripStatusId('Approved');

        return FleetTripLog::where('Status', $approvedStatusId)
            ->whereNull('ParentTripID') // Only parent trips
            ->orderByDesc('CreatedOn')
            ->get(['Id', 'TripNo', 'TripStartDate', 'StartLocation', 'EndLocation']);
    }
}
