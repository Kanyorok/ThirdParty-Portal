<?php

namespace App\Services\FleetManagement;

use App\Models\Fleet\FleetVehicleInspection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Enums\Core\ModulesEnum;
use Illuminate\Http\UploadedFile;

class FleetVehicleInspectionService
{
    /**
     * Generate an inspection number.
     * - If post-trip → reuse parent’s number
     * - If pre-trip → generate a new one
     */

    protected function generateInspectionNo()
    {
        $lastInspection = FleetVehicleInspection::withTrashed()->latest('CreatedOn')->first();

        if (!$lastInspection) {
            return 'INSP-0001';
        }

        // Extract number from last ID
        $lastNumber = (int) str_replace('INSP-', '', $lastInspection->InspectionID);
        

        // Increment
        $nextNumber = $lastNumber + 1;

        return 'INSP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }


    /**
     * Create a new vehicle inspection (pre or post trip)
     */

    
   public function create(array $data,UploadedFile $document = null): FleetVehicleInspection
    {
        return DB::transaction(function () use ($data,$document) {
            $parent = !empty($data['ParentInspectionID'])
                ? FleetVehicleInspection::find($data['ParentInspectionID'])
                : null;

            $inspection = FleetVehicleInspection::create([
                'InspectionID'       => $this->generateInspectionNo($parent),
                'ParentInspectionID' => $parent?->Id,
                'VehicleID'          => $data['VehicleID'],
                'FuelType'           => $data['FuelType'],
                'DriverID'           => $data['DriverID'],
                'InspectionDate'     => $data['InspectionDate'] ?? null,
                'Mileage'            => $data['Mileage'] ?? null,
                'Fuel'               => $data['Fuel'] ?? null,
                'EngineOil'          => $data['EngineOil'] ?? null,
                'Speedometer'        => $data['Speedometer'] ?? null,
                'Coolant'            => $data['Coolant'] ?? null,
                'Reflector'          => $data['Reflector'] ?? 0,
                'FireExtinguisher'   => $data['FireExtinguisher'] ?? 0,
                'FirstAidKit'        => $data['FirstAidKit'] ?? 0,
                'SpareTyre'          => $data['SpareTyre'] ?? 0,
                'Spanner'            => $data['Spanner'] ?? 0,
                'Jack'               => $data['Jack'] ?? 0,
                '4XFloorMats'        => $data['4XFloorMats'] ?? 0,
                'CreatedBy'          => Auth::id(),
                'CreatedOn'          => now(),
                'ModifiedBy'         => Auth::id(),
                'ModifiedOn'         => now(),
            ]);

           
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

            return $inspection;
        });
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
