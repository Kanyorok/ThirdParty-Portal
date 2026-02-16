<?php

namespace App\Services\FleetManagement;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetVehicleAssignmentService
{
    /**
     * Generate an assignment number.
     */
    protected function generateAssignmentNo()
    {
        $lastInspection = FleetVehicleAssignment::withTrashed()->latest('CreatedOn')->first();

        if (! $lastInspection) {
            return 'ASG-0001';
        }

        // Extract number from last ID
        $lastNumber = (int)str_replace('ASG-', '', $lastInspection->AssignmentID);
        $nextNumber = $lastNumber + 1;

        return 'ASG-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new vehicle assignment
     */
    public function create(array $data): FleetVehicleAssignment
    {
        return DB::transaction(function () use ($data) {
            $assignment = FleetVehicleAssignment::create([
                'AssignmentID' => $this->generateAssignmentNo(),
                'TripNo' => $data['TripNo'],
                'VehicleType' => $data['VehicleType'],
                'DriverID' => $data['DriverID'],
                'VehicleID' => $data['VehicleID'],
                'LastInspectionDate' => $data['LastInspectionDate'],
                'AssignmentDate' => $data['AssignmentDate'] ?? null,
                'Purpose' => $data['Purpose'] ?? null,
                'Notes' => $data['Notes'] ?? null,
                'AssignedBy' => $data['AssignedBy'] ?? null,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
            ]);

            // Update the vehicle status to "AssignedTrip"
            $this->updateVehicleStatus($data['VehicleID'], 'AssignedTrip');

            // Update the driver status to "AssignedTrip"
            $this->updateDriverStatus($data['DriverID'], 'AssignedTrip');

            activity()
                ->causedBy(Auth::user())
                ->performedOn($assignment)
                ->event('created')
                ->log("Created vehicle assignment: {$assignment->AssignmentID}");

            return $assignment;
        });
    }

    /**
     * Update an existing assignment
     */
    public function update(FleetVehicleAssignment $assignment, array $data): FleetVehicleAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $oldVehicleId = $assignment->VehicleID;
            $oldDriverId = $assignment->DriverID;

            $data['ModifiedBy'] = Auth::id();
            $data['ModifiedOn'] = now();

            $assignment->update($data);

            // If vehicle changed, update statuses
            if (isset($data['VehicleID']) && $data['VehicleID'] != $oldVehicleId) {
                // Set old vehicle back to "Available" if no other assignments
                $this->revertVehicleStatusIfNoAssignment($oldVehicleId);

                // Set new vehicle to "AssignedTrip"
                $this->updateVehicleStatus($data['VehicleID'], 'AssignedTrip');
            }

            // If driver changed, update statuses
            if (isset($data['DriverID']) && $data['DriverID'] != $oldDriverId) {
                // Set old driver back to "Available" if no other assignments
                $this->revertDriverStatusIfNoAssignment($oldDriverId);

                // Set new driver to "AssignedTrip"
                $this->updateDriverStatus($data['DriverID'], 'AssignedTrip');
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($assignment)
                ->event('updated')
                ->withProperties(['attributes' => $data])
                ->log("Updated vehicle assignment: {$assignment->AssignmentID}");

            return $assignment;
        });
    }

    /**
     * Delete an assignment (soft delete)
     */
    public function delete(FleetVehicleAssignment $assignment): bool
    {
        return DB::transaction(function () use ($assignment) {
            $vehicleId = $assignment->VehicleID;
            $driverId = $assignment->DriverID;

            $assignment->DeletedBy = Auth::id();
            $assignment->DeletedOn = now();
            $assignment->save();

            $assignment->delete();

            // Set vehicle back to "Available" if no other active assignments
            $this->revertVehicleStatusIfNoAssignment($vehicleId);

            // Set driver back to "Available" if no other active assignments
            $this->revertDriverStatusIfNoAssignment($driverId);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($assignment)
                ->event('deleted')
                ->log("Deleted vehicle assignment");

            return true;
        });
    }

    /**
     * Update vehicle status directly
     */
    private function updateVehicleStatus(int $vehicleId, string $statusDescription): void
    {
        $statusId = CodeDetail::where('CodeID', 'VehicleAvailabilityStatus')
            ->where('Description', $statusDescription)
            ->value('ID');

        if ($statusId) {
            $vehicle = FleetVehicle::find($vehicleId);
            if ($vehicle) {
                $vehicle->VehicleStatus = $statusId;
                $vehicle->ModifiedBy = Auth::id();
                $vehicle->ModifiedOn = now();
                $vehicle->save();

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($vehicle)
                    ->log("Vehicle status updated to {$statusDescription} after assignment");
            }
        }
    }

    /**
     * Update driver status directly
     */
    private function updateDriverStatus(int $driverId, string $statusDescription): void
    {
        $statusId = CodeDetail::where('CodeID', 'DriverAvailabilityStatus')
            ->where('Description', $statusDescription)
            ->value('ID');

        if ($statusId) {
            $driver = FleetDriver::find($driverId);
            if ($driver) {
                $driver->DriverStatus = $statusId;
                $driver->ModifiedBy = Auth::id();
                $driver->ModifiedOn = now();
                $driver->save();

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($driver)
                    ->log("Driver status updated to {$statusDescription} after assignment");
            }
        }
    }

    /**
     * Revert vehicle status to "Available" if no other active assignments
     */
    private function revertVehicleStatusIfNoAssignment(int $vehicleId): void
    {
        // Check if there are any other active assignments for this vehicle
        $activeAssignments = FleetVehicleAssignment::where('VehicleID', $vehicleId)
            ->whereNull('DeletedOn')
            ->count();

        if ($activeAssignments === 0) {
            $this->updateVehicleStatus($vehicleId, 'Available');
        }
    }

    /**
     * Revert driver status to "Available" if no other active assignments
     */
    private function revertDriverStatusIfNoAssignment(int $driverId): void
    {
        // Check if there are any other active assignments for this driver
        $activeAssignments = FleetVehicleAssignment::where('DriverID', $driverId)
            ->whereNull('DeletedOn')
            ->count();

        if ($activeAssignments === 0) {
            $this->updateDriverStatus($driverId, 'Available');
        }
    }
}
