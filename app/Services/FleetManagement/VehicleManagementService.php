<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetVehicle;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VehicleManagementService
{
    /**
     * Create a new Fleet Vehicle
     */
    public function create(array $data): FleetVehicle
    {
        return DB::transaction(function () use ($data) {

            if (FleetVehicle::where('RegistrationNo', $data['RegistrationNo'])->exists()) {
                throw new \Exception('The Registration Number already exists.');
            }

            if (!empty($data['ChassisNumber']) && FleetVehicle::where('ChassisNumber', $data['ChassisNumber'])->exists()) {
                throw new \Exception('The Chassis Number already exists.');
            }

            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            $vehicle = FleetVehicle::create($data);

            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->log('Fleet Vehicle Created');

            return $vehicle;
        });
    }

    /**
     * Update an existing Fleet Vehicle
     */
    public function update(FleetVehicle $vehicle, array $data): FleetVehicle
    {
        return DB::transaction(function () use ($vehicle, $data) {

            $vehicle->fill($data);
            $vehicle->ModifiedBy = Auth::id();
            $vehicle->ModifiedOn = now();
            $vehicle->save();

            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Fleet Vehicle Updated');

            return $vehicle;
        });
    }

    /**
     * Soft delete a Fleet Vehicle
     */
    public function delete(FleetVehicle $vehicle): bool
    {
        return DB::transaction(function () use ($vehicle) {

            $vehicle->DeletedBy = Auth::id();
            $vehicle->DeletedOn = now();
            $vehicle->save();

            $vehicle->delete(); // Soft delete

            activity()
                ->performedOn($vehicle)
                ->causedBy(Auth::user())
                ->log('Fleet Vehicle Deleted');

            return true;
        });
    }
}
