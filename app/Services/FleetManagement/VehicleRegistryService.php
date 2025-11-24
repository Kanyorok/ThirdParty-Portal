<?php

namespace App\Services\FleetManagement;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\FleetManagement\FleetMake;
use App\Models\FleetManagement\VehicleRegistry;
use App\Models\FleetManagement\FleetModel;
use App\Models\Auth\User;
use App\Http\Requests\FleetManagement\VehicleRegistryRequest;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;


class VehicleRegistryService
{

    public function create(array $data): VehicleRegistry
    {
        return DB::transaction(function () use ($data) {

            // Check for duplicate Registration Number
            if (VehicleRegistry::where('RegistrationNo', $data['RegistrationNo'])->exists()) {
                throw new \Exception('The Registration Number already exists.');
            }

            // Check for duplicate ChassisNo Number
            if (VehicleRegistry::where('ChassisNo', $data['ChassisNo'])->exists()) {
                throw new \Exception('The Chassis No already exists.');
            }

            $data['RegistrationNo'] = $data['RegistrationNo'] ?? null;
            $data['Model'] = $data['Model'] ?? null;
            $data['Make'] = $data['Make'] ?? null;
            $data['Type'] = $data['Type'] ?? null;
            $data['Color'] = $data['Color'] ?? null;
            $data['Year'] = $data['Year'] ?? null;
            $data['ChassisNo'] = $data['ChassisNo'] ?? null;
            $data['EngineNo'] = $data['EngineNo'] ?? null;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            $vehicle = VehicleRegistry::create($data);

            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->log('Vehicle Registry Created');

            return $vehicle;
        });
    }


    public function update(VehicleRegistry $vehicle, array $data): VehicleRegistry
    {
        return DB::transaction(function () use ($vehicle, $data) {
            $vehicle->update($data);
            $vehicle->ModifiedBy = Auth::id();
            $vehicle->ModifiedOn = now();
            $vehicle->save();

            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Fleet Make Updated');

            return $vehicle;
        });
    }

    public function delete(VehicleRegistry $vehicle): bool
    {
        return DB::transaction(function () use ($vehicle) {

            $vehicle->DeletedBy = Auth::id();
            $vehicle->DeletedOn = now();
            $vehicle->save();

            $vehicle->delete();

            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->log('Fleet Make Deleted');

            return true;
        });
    }
}


