<?php

namespace App\Services\FleetManagement;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleAssignment;
use App\Models\Fleet\FleetVehicleInspection;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetVehicleInspectionService
{
    /**
     * Generate an inspection number.
     * Always produce a new unique inspection number.
     */
    protected function generateInspectionNo()
    {
        // Always generate a new sequential inspection id (do NOT reuse parent InspectionID)
        $lastInspection = FleetVehicleInspection::withTrashed()->latest('CreatedOn')->first();

        if (! $lastInspection || empty($lastInspection->InspectionID)) {
            return 'INSP-0001';
        }

        // Extract numeric part safely
        $lastNumber = (int) preg_replace('/\D/', '', $lastInspection->InspectionID);

        $nextNumber = $lastNumber + 1;

        return 'INSP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new vehicle inspection (pre or post trip)
     */
    public function create(array $data, UploadedFile $document = null): FleetVehicleInspection
    {
        return DB::transaction(function () use ($data, $document) {
            // Get authenticated user ID BEFORE the transaction
            $userId = Auth::id();
            if (! $userId) {
                throw new Exception('User not authenticated. Cannot create inspection.');
            }

            $parent = ! empty($data['ParentInspectionID'])
                ? FleetVehicleInspection::find($data['ParentInspectionID'])
                : null;

            $inspection = FleetVehicleInspection::create([
                'InspectionID' => $this->generateInspectionNo(),
                'ParentInspectionID' => $parent?->Id,
                'InspectionTypeID' => $data['InspectionTypeID'],
                'VehicleID' => $data['VehicleID'],
                'FuelType' => $data['FuelType'],
                'DriverID' => $data['DriverID'] ?? null,
                'ContractedDriverID' => $data['ContractedDriverID'] ?? null,
                'InspectionDate' => $data['InspectionDate'] ?? null,
                'Mileage' => $data['Mileage'] ?? null,
                'Fuel' => $data['Fuel'] ?? null,
                'EngineOil' => $data['EngineOil'] ?? null,
                'Coolant' => $data['Coolant'] ?? null,
                'Reflector' => $data['Reflector'] ?? 0,
                'FireExtinguisher' => $data['FireExtinguisher'] ?? 0,
                'FirstAidKit' => $data['FirstAidKit'] ?? 0,
                'SpareTyre' => $data['SpareTyre'] ?? 0,
                'Spanner' => $data['Spanner'] ?? 0,
                'Jack' => $data['Jack'] ?? 0,
                '4XFloorMats' => $data['4XFloorMats'] ?? 0,
                'CreatedBy' => $userId,
                'CreatedOn' => now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);

            // If document provided, attach using existing helper
            if ($document) {
                $inspection->newDocument(
                    ModulesEnum::Fleet,
                    $document,
                    [PermissionEnum::VehicleInspectionView->value],
                    Auth::user()
                );
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($inspection)
                ->event('created')
                ->log(
                    $parent
                        ? "Created POST trip inspection linked to {$parent->InspectionID}"
                        : "Created PRE trip inspection: {$inspection->InspectionID}"
                );

            // When creating a POST-TRIP inspection, mark related trip as Completed
            // AND update vehicle and driver statuses back to "Available"
            if ($parent) {
                $this->updateRelatedTripStatus($inspection, $userId);
            }

            return $inspection;
        });
    }

    /**
     * Update related trip status when post-trip inspection is created
     * Also update vehicle and driver statuses back to "Available"
     */
    private function updateRelatedTripStatus(FleetVehicleInspection $inspection, int $userId): void
    {
        // Get CodeDetail ID for 'Completed' trip status
        $completedStatusId = CodeDetail::where('CodeID', 'TripStatus')
            ->where('Description', 'Completed')
            ->value('ID');

        if (! $completedStatusId) {
            return;
        }

        $trip = $this->findRelatedTrip($inspection, $completedStatusId);

        // Update trip if found and not already completed
        if ($trip && $trip->Status != $completedStatusId) {
            $trip->Status = $completedStatusId;
            $trip->ModifiedBy = $userId;
            $trip->ModifiedOn = now();
            $trip->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($trip)
                ->event('completed')
                ->log("Trip {$trip->TripNo} marked as Completed after post-trip inspection");

            // Now update vehicle and driver statuses back to "Available"
            $this->updateVehicleAndDriverStatuses($inspection, $trip->Id, $userId);
        }
    }

    /**
     * Update vehicle and driver statuses back to "Available" after trip completion
     */
    private function updateVehicleAndDriverStatuses(FleetVehicleInspection $inspection, int $tripId, int $userId): void
    {
        // Get status IDs for "Available"
        $vehicleAvailableId = CodeDetail::where('CodeID', 'VehicleAvailabilityStatus')
            ->where('Description', 'Available')
            ->value('ID');

        $driverAvailableId = CodeDetail::where('CodeID', 'DriverAvailabilityStatus')
            ->where('Description', 'Available')
            ->value('ID');

        if (! $vehicleAvailableId || ! $driverAvailableId) {
            return;
        }

        // Update vehicle status to "Available" if no other active assignments
        $this->updateVehicleStatus($inspection->VehicleID, $vehicleAvailableId, $tripId, $userId);

        // Update driver status to "Available" (check both regular and contracted drivers)
        if ($inspection->DriverID) {
            $this->updateDriverStatus($inspection->DriverID, $driverAvailableId, $tripId, $userId, 'regular');
        }

        if ($inspection->ContractedDriverID) {
            $this->updateDriverStatus($inspection->ContractedDriverID, $driverAvailableId, $tripId, $userId, 'contracted');
        }
    }

    /**
     * Update vehicle status back to "Available" after trip completion
     */
    private function updateVehicleStatus(int $vehicleId, int $availableStatusId, int $completedTripId, int $userId): void
    {
        $vehicle = FleetVehicle::find($vehicleId);
        if (! $vehicle) {
            return;
        }

        // Check if vehicle has any other active assignments (excluding the completed trip)
        $activeAssignments = FleetVehicleAssignment::where('VehicleID', $vehicleId)
            ->where('TripNo', '!=', $completedTripId) // Exclude the completed trip
            ->whereHas('trip', function ($query) {
                // Get trips that are not completed
                $completedStatusId = CodeDetail::where('CodeID', 'TripStatus')
                    ->where('Description', 'Completed')
                    ->value('ID');
                $query->where('Status', '!=', $completedStatusId);
            })
            ->whereNull('DeletedOn')
            ->count();

        // Only update to "Available" if no other active assignments
        if ($activeAssignments === 0) {
            $oldStatus = $this->getStatusName($vehicle->VehicleStatus, 'VehicleAvailabilityStatus');
            $vehicle->VehicleStatus = $availableStatusId;
            $vehicle->ModifiedBy = $userId;
            $vehicle->ModifiedOn = now();
            $vehicle->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($vehicle)
                ->event('status-updated')
                ->log("Vehicle status updated from {$oldStatus} to Available after post-trip inspection");
        } else {
            activity()
                ->causedBy(Auth::user())
                ->performedOn($vehicle)
                ->event('status-not-changed')
                ->log("Vehicle status not changed to Available - has {$activeAssignments} other active assignments");
        }
    }

    /**
     * Update driver status back to "Available" after trip completion
     */
    private function updateDriverStatus(int $driverId, int $availableStatusId, int $completedTripId, int $userId, string $driverType = 'regular'): void
    {
        $driver = $driverType === 'regular'
            ? FleetDriver::find($driverId)
            : null; // For contracted drivers, you might need a different model

        if (! $driver && $driverType === 'regular') {
            return;
        }

        // For regular drivers, check if they have any other active assignments
        if ($driverType === 'regular') {
            $activeAssignments = FleetVehicleAssignment::where('DriverID', $driverId)
                ->where('TripNo', '!=', $completedTripId) // Exclude the completed trip
                ->whereHas('trip', function ($query) {
                    // Get trips that are not completed
                    $completedStatusId = CodeDetail::where('CodeID', 'TripStatus')
                        ->where('Description', 'Completed')
                        ->value('ID');
                    $query->where('Status', '!=', $completedStatusId);
                })
                ->whereNull('DeletedOn')
                ->count();

            // Only update to "Available" if no other active assignments
            if ($activeAssignments === 0) {
                $oldStatus = $this->getStatusName($driver->DriverStatus, 'DriverAvailabilityStatus');
                $driver->DriverStatus = $availableStatusId;
                $driver->ModifiedBy = $userId;
                $driver->ModifiedOn = now();
                $driver->save();

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($driver)
                    ->event('status-updated')
                    ->log("Driver status updated from {$oldStatus} to Available after post-trip inspection");
            } else {
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($driver)
                    ->event('status-not-changed')
                    ->log("Driver status not changed to Available - has {$activeAssignments} other active assignments");
            }
        }
        // For contracted drivers - you might need different logic
        // Since we don't have FleetVehicleAssignment for contracted drivers
        // You might need to create a separate table or handle differently
    }

    /**
     * Get status name by ID
     */
    private function getStatusName(?int $statusId, string $codeId): string
    {
        if (! $statusId) {
            return 'Unknown';
        }

        $status = CodeDetail::where('CodeID', $codeId)
            ->where('ID', $statusId)
            ->first();

        return $status ? $status->Description : "Unknown (ID: {$statusId})";
    }

    /**
     * Find related trip for the inspection
     */
    private function findRelatedTrip(FleetVehicleInspection $inspection, int $completedStatusId): ?FleetTripLog
    {
        $ongoingStatusId = CodeDetail::where('CodeID', 'TripStatus')
            ->where('Description', 'Ongoing')
            ->value('ID');

        // 1) Try to find assignment -> Trip that is Ongoing
        $assignment = FleetVehicleAssignment::where('VehicleID', $inspection->VehicleID)
            ->whereNotNull('TripNo')
            ->orderByDesc('AssignmentDate')
            ->first();

        if ($ongoingStatusId && $assignment && $assignment->TripNo) {
            $trip = $this->findTripByReference($assignment->TripNo, $ongoingStatusId);
            if ($trip) {
                return $trip;
            }
        }

        // 2) Fallback: search assignment TripNos for an Ongoing trip
        if ($ongoingStatusId) {
            $tripNos = FleetVehicleAssignment::where('VehicleID', $inspection->VehicleID)
                ->whereNotNull('TripNo')
                ->pluck('TripNo')
                ->filter()
                ->values();

            $numericIds = $tripNos->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (int)$v)->unique()->values()->all();
            $stringTripNos = $tripNos->filter(fn ($v) => ! is_numeric($v))->unique()->values()->all();

            $query = FleetTripLog::query();
            $query->where('Status', $ongoingStatusId);

            $query->where(function ($q) use ($numericIds, $stringTripNos) {
                if (! empty($numericIds)) {
                    $q->whereIn('Id', $numericIds);
                }
                if (! empty($stringTripNos)) {
                    $q->orWhereIn('TripNo', $stringTripNos);
                }
            });

            $trip = $query->orderByDesc('TripStartDate')->first();
            if ($trip) {
                return $trip;
            }
        }

        // 3) Final fallback: most recent Ongoing trip overall
        if ($ongoingStatusId) {
            $trip = FleetTripLog::where('Status', $ongoingStatusId)
                ->orderByDesc('TripStartDate')
                ->first();
            if ($trip) {
                return $trip;
            }
        }

        // 4) If still not found, fall back to previous logic (any non-completed trip)
        if ($assignment && $assignment->TripNo) {
            if (is_numeric($assignment->TripNo)) {
                $trip = FleetTripLog::find((int)$assignment->TripNo);
            } else {
                $trip = FleetTripLog::where('TripNo', $assignment->TripNo)->first();
            }
            if ($trip && $trip->Status != $completedStatusId) {
                return $trip;
            }
        }

        // 5) Fallback: search assignment TripNos for any non-completed trip
        $tripNos = FleetVehicleAssignment::where('VehicleID', $inspection->VehicleID)
            ->whereNotNull('TripNo')
            ->pluck('TripNo')
            ->filter()
            ->values();

        $numericIds = $tripNos->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (int)$v)->unique()->values()->all();
        $stringTripNos = $tripNos->filter(fn ($v) => ! is_numeric($v))->unique()->values()->all();

        $query = FleetTripLog::query();
        $query->where('Status', '!=', $completedStatusId);

        $query->where(function ($q) use ($numericIds, $stringTripNos) {
            if (! empty($numericIds)) {
                $q->whereIn('Id', $numericIds);
            }
            if (! empty($stringTripNos)) {
                $q->orWhereIn('TripNo', $stringTripNos);
            }
        });

        $trip = $query->orderByDesc('TripStartDate')->first();
        if ($trip) {
            return $trip;
        }

        // 6) Final fallback: most recent non-completed trip overall
        return FleetTripLog::where('Status', '!=', $completedStatusId)
            ->orderByDesc('TripStartDate')
            ->first();
    }

    /**
     * Helper to find trip by reference (ID or TripNo)
     */
    private function findTripByReference($reference, int $statusId = null): ?FleetTripLog
    {
        $query = FleetTripLog::query();

        if ($statusId) {
            $query->where('Status', $statusId);
        }

        if (is_numeric($reference)) {
            $query->where('Id', (int)$reference);
        } else {
            $query->where('TripNo', $reference);
        }

        return $query->first();
    }

    /**
     * Update an existing inspection
     */
    public function update(FleetVehicleInspection $inspection, array $data): FleetVehicleInspection
    {
        return DB::transaction(function () use ($inspection, $data) {
            $data['ModifiedBy'] = Auth::id();
            $data['ModifiedOn'] = now();

            $inspection->update($data);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($inspection)
                ->event('updated')
                ->withProperties(['attributes' => $data])
                ->log("Updated vehicle inspection: {$inspection->InspectionID}");

            return $inspection;
        });
    }

    /**
     * Delete an inspection (soft delete)
     */
    public function delete(FleetVehicleInspection $inspection): bool
    {
        return DB::transaction(function () use ($inspection) {
            $inspection->DeletedBy = Auth::id();
            $inspection->DeletedOn = now();
            $inspection->save();

            $inspection->delete();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($inspection)
                ->event('deleted')
                ->log("Deleted vehicle inspection: {$inspection->InspectionID}");

            return true;
        });
    }
}
