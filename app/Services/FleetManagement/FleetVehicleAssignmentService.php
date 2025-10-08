<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetVehicleAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Enums\Core\ModulesEnum;

class FleetVehicleAssignmentService
{
    /**
     * Generate an assignment number.
     * - If post-trip → reuse parent’s number
     * - If pre-trip → generate a new one
     */

    protected function generateAssignmentNo()
    {
        $lastInspection = FleetVehicleAssignment::withTrashed()->latest('CreatedOn')->first();

        if (!$lastInspection) {
            return 'ASG-0001';
        }

        // Extract number from last ID
        $lastNumber = (int) str_replace('ASG-', '', $lastInspection->AssignmentID);


        // Increment
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
                'AssignmentID'       => $this->generateAssignmentNo(),
                'TripNo'             => $data['TripNo'],
                'VehicleType'        => $data['VehicleType'],
                'DriverID'        => $data['DriverID'],
                'VehicleID'          => $data['VehicleID'],
                'LastInspectionDate' => $data['LastInspectionDate'],
                'AssignmentDate'     => $data['AssignmentDate'] ?? null,
                'Purpose'            => $data['Purpose'] ?? null,
                'Notes'              => $data['Notes'] ?? null,
                'AssignedBy'         => $data['AssignedBy'] ?? null,
                'CreatedBy'          => Auth::id(),
                'CreatedOn'          => now(),

            ]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($assignment)
                ->event('created')
                ->log("Created vehicle assignment: {$assignment->AssignmentID}");

            return $assignment;
        });
    }

    /**
     * Update an existing inspection
     */
    public function update(FleetVehicleAssignment $assignment, array $data): FleetVehicleAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $data['ModifiedBy'] = Auth::id();
            $data['ModifiedOn'] = now();

            $assignment->update($data);

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
     * Delete an inspection (soft delete)
     */

    public function delete(FleetVehicleAssignment $assignment): bool
    {
        return DB::transaction(function () use ($assignment) {
            $assignment->DeletedBy = Auth::id();
            $assignment->DeletedOn = now();
            $assignment->save();

            $assignment->delete();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($assignment)
                ->event('deleted')
                ->log("Deleted vehicle assignment");

            return true;
        });
    }

}
