<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetContractedDriverAssignment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FleetContractedDriverAssignmentService
{
    /**
     * Create a new assignment
     */
    public function create(array $data): FleetContractedDriverAssignment
    {
        return DB::transaction(function () use ($data) {
            $assignment = FleetContractedDriverAssignment::create([
                'DriverID' => $data['DriverID'] ?? null,
                'VehicleID' => $data['VehicleID'] ?? null,
                'AssignmentDate' => $data['AssignmentDate'] ?? null,
                'UnassignmentDate' => $data['UnassignmentDate'] ?? null,
                'Purpose' => $data['Purpose'] ?? null,
                'AssignedBy' => $data['AssignedBy'] ?? null,
                'Notes' => $data['Notes'] ?? null,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now()
            ]);

            activity()
                ->performedOn($assignment)
                ->causedBy(Auth::user())
                ->log('Contracted Driver assignment Created');

            return $assignment;
        });
    }

    /**
     * Update assignment
     */
    public function update(FleetContractedDriverAssignment $assignment, array $data): FleetContractedDriverAssignment
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
                ->log('Contracted Driver assignment Updated');

            return $assignment;
        });
    }

    /**
     * Soft delete a assignment
     */
    public function delete(FleetContractedDriverAssignment $assignment): bool
    {
        return DB::transaction(function () use ($assignment) {
            $assignment->DeletedBy = Auth::id();
            $assignment->DeletedOn = now();
            $assignment->save();

            $assignment->delete(); // Soft delete

            activity()
                ->performedOn($assignment)
                ->causedBy(Auth::user())
                ->log('Contracted Driver assignment Deleted');

            return true;
        });
    }
}
