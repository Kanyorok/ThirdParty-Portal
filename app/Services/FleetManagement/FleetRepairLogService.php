<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetRepairLog;

class FleetRepairLogService
{
    /**
     * Create a new Repair Log
     */
    public function create(array $data): FleetRepairLog
    {
        return DB::transaction(function () use ($data) {
            $data['RepairID'] = $this->generateRepairID();
            $data['VehicleID'] = $data['VehicleID'] ?? null;
            $data['RepairType'] = $data['RepairType'] ?? null;
            $data['RepairDate'] = $data['RepairDate'] ?? null;
            $data['Vendor'] = $data['Vendor'] ?? null;
            $data['Cost'] = $data['Cost'] ?? null;
            $data['Description'] = $data['Description'] ?? null;
            $data['Notes'] = $data['Notes'] ?? null;
            $data['ScheduleID'] = $data['ScheduleID'] ?? null;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            $schedule = FleetRepairLog::create($data);

            activity()
                ->performedOn($schedule)
                ->causedBy(Auth::user())
                ->log('Repair Log Created');

            return $schedule;
        });
    }

    /**
     * Generate the next Repair Log number
     */
    private function generateRepairID(): string
    {
        $latestRepair = FleetRepairLog::withTrashed()->latest('CreatedOn')->first();

        if (!$latestRepair || !$latestRepair->RepairID) {
            return 'REP-0001';
        }

        $lastId = (int) str_replace('REP-', '', $latestRepair->RepairID);
        $newId = $lastId + 1;

        return 'REP-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update Repair Log by ID
     */
    public function update(int $id, array $data): FleetRepairLog
    {
        return DB::transaction(function () use ($id, $data) {
            $records = FleetRepairLog::findOrFail($id);

            $records->fill($data);
            $records->ModifiedBy = Auth::id();
            $records->ModifiedOn = now();
            $records->save();

            activity()
                ->performedOn($records)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Repair Log Updated');

            return $records;
        });
    }

    /**
     * Soft delete a Fleet Repair Log by ID
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $records = FleetRepairLog::findOrFail($id);

            $records->DeletedBy = Auth::id();
            $records->DeletedOn = now();
            $records->save();

            $records->delete(); // Soft delete

            activity()
                ->performedOn($records)
                ->causedBy(Auth::user())
                ->log('Repair Log Deleted');

            return true;
        });
    }
}
