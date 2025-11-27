<?php

namespace App\Services\FleetManagement;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\FleetManagement\DriverManagement;
use App\Models\Auth\User;
use App\Http\Requests\FleetManagement\DriverManagementRequest;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;


class DriverManagementService
{

    public function createDriver(array $data): DriverManagement
    {
        return DB::transaction(function () use ($data) {
            $data['DriverID'] = $this->generateDriverID();
            $data['DriverName'] = $data['DriverName'] ?? null;
            $data['LicenseNumber'] = $data['LicenseNumber'] ?? null;
            $data['LicenseExpiryDate'] = $data['LicenseExpiryDate'] ?? null;
            $data['Phone'] = $data['Phone'] ?? null;
            $data['Email'] = $data['Email'] ?? null;
            $data['Remarks'] = $data['Remarks'] ?? null;
            $data['EmploymentStatus'] = $data['EmploymentStatus'] ?? true;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            return DriverManagement::create($data);
        });
        activity()
            ->performedOn($driver)
            ->causedBy(Auth::user())
            ->log('Driver Created');
    }


    private function generateDriverID(): string
    {
        $latestDriver = DriverManagement::withTrashed()->latest('CreatedOn')->first();

        if (!$latestDriver || !$latestDriver->DriverID) {
            return 'DRV-0001';
        }

        $lastId = (int)str_replace('DRV-', '', $latestDriver->DriverID);
        $newId = $lastId + 1;

        return 'DRV-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }


    public function updateDriver(DriverManagement $driver, array $data): DriverManagement
    {
        return DB::transaction(function () use ($driver, $data) {

            $driver->update($data);
            $driver->ModifiedBy = Auth::id();
            $driver->ModifiedOn = now();
            $driver->save();
            return $driver;
        });
        activity()
            ->performedOn($driver)
            ->causedBy(Auth::user())
            ->withProperties(['attributes' => $data])
            ->log('Driver Updated');
    }

    public function deleteDriver(DriverManagement $driver): bool
    {
        return DB::transaction(function () use ($driver) {

            $driver->DeletedBy = Auth::id();
            $driver->DeletedOn = now();
            $driver->save();

            $driver->delete();

            activity()
                ->performedOn($driver)
                ->causedBy(Auth::user())
                ->log('Driver Deleted');

            return true;
        });
    }
}


