<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetInspectionSchedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FleetInspectionScheduleService
{
    /**
     * Create a new Inspection
     */

    public function create(array $data): FleetInspectionSchedule
    {
        return DB::transaction(function () use ($data) {
            $data['InspectionNo'] = $this->generateInspectionNo();
            $data['VehicleID'] = $data['VehicleID'] ?? null;
            $data['InspectionType'] = $data['InspectionType'] ?? null;
            $data['InspectionDate'] = $data['InspectionDate'] ?? null;
            $data['DueDate'] = $data['DueDate'] ?? null;
            $data['Status'] = $data['Status'] ?? null;
            $data['Remarks'] = $data['Remarks'] ?? null;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();


            return FleetInspectionSchedule::create($data);
        });
        activity()
            ->performedOn($driver)
            ->causedBy(Auth::user())
            ->log('Inspection Created');
    }

    private function generateInspectionNo(): string
    {
        $latestInspection = FleetInspectionSchedule::withTrashed()->latest('CreatedOn')->first();

        if (!$latestInspection || !$latestInspection->InspectionNo) {
            return 'INS-0001';
        }

        $lastId = (int)str_replace('INS-', '', $latestInspection->InspectionNo);
        $newId = $lastId + 1;

        return 'INS-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }


    /**
     * Update Inspection
     */
    public function update(FleetInspectionSchedule $records, array $data): FleetInspectionSchedule
    {
        return DB::transaction(function () use ($records, $data) {

            $records->fill($data);
            $records->ModifiedBy = Auth::id();
            $records->ModifiedOn = now();
            $records->save();

            activity()
                ->performedOn($records)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Fleet Inspection Updated');

            return $records;
        });
    }

    /**
     * Soft delete a Fleet Vehicle
     */
    public function delete(FleetInspectionSchedule $records): bool
    {
        return DB::transaction(function () use ($records) {

            $records->DeletedBy = Auth::id();
            $records->DeletedOn = now();
            $records->save();

            $records->delete(); // Soft delete

            activity()
                ->performedOn($records)
                ->causedBy(Auth::user())
                ->log('Fleet Inspection Deleted');

            return true;
        });
    }
}
