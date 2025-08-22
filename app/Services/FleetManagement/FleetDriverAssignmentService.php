<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetDriverAssignment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FleetDriverAssignmentService
{
    /**
     * Create a new assignment
     */
    public function create(array $data): FleetDriverAssignment
    {
        return DB::transaction(function () use ($data) {
            $assignment = FleetDriverAssignment::create([
                'DriverID' => $data['DriverID'] ?? null,
                'VehicleID' => $data['VehicleID'] ?? null,
                'AssignmentDate'      => $data['AssignmentDate'] ?? null,
                'UnassignmentDate'    => $data['UnassignmentDate'] ?? null,
                'Purpose'          => $data['Purpose'] ?? null,
                'AssignedBy'          => $data['AssignedBy'] ?? null,
                'Notes'              => $data['Notes'] ?? null,
                'CreatedBy'        =>  $data['CreatedBy'] = Auth::id(),
                'CreatedOn'        =>  $data['CreatedOn'] = now()
            ]);

            activity()
                ->performedOn($assignment)
                ->causedBy(Auth::user())
                ->log('Driver assignment Created');

            return $assignment;
        });
    }

    /**
     * Update assignment
     */
    public function update(FleetDriverAssignment $assignment, array $data): FleetDriverAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $assignment->fill($data);
            $assignment->ModifiedBy = Auth::id();
            $assignment->ModifiedOn = now();
            $assignment->save();

            activity()
                ->performedOn($assignment)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log(' Driver assignment Updated');

            return $assignment;
        });
    }

    /**
     * Soft delete a assignment
     */
    public function delete(FleetDriverAssignment $assignment): bool
    {
        return DB::transaction(function () use ($assignment) {
            $assignment->DeletedBy = Auth::id();
            $assignment->DeletedOn = now();
            $assignment->save();

            $assignment->delete(); // Soft delete

            activity()
                ->performedOn($assignment)
                ->causedBy(Auth::user())
                ->log(' Driver assignment Deleted');

            return true;
        });
    }
}
