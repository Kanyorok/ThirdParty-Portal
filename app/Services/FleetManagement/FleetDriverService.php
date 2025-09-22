<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetDriver;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use Illuminate\Http\UploadedFile;

class FleetDriverService
{

    /**
     * Create a new Driver
     */

    public function create(array $data, UploadedFile $document = null): FleetDriver
    {
        return DB::transaction(function () use ($data, $document) {
            $data['DriverNo'] = $this->generateDriverNo();
            $data['FullName'] = $data['FullName'] ?? null;
            $data['StaffNumber'] = $data['StaffNumber'] ?? null;
            $data['NationalID'] = $data['NationalID'] ?? null;
            $data['Phone'] = $data['Phone'] ?? null;
            $data['Email'] = $data['Email'] ?? null;
            $data['EmploymentType'] = $data['EmploymentType'] ?? null;
            $data['Notes'] = $data['Notes'] ?? null;
            $data['IsActive'] = $data['IsActive'] ?? 1;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            $driver = FleetDriver::create($data);

            if ($document) {
                $driver->newDocument(
                    ModulesEnum::Fleet,
                    $document,
                    [PermissionEnum::FleetDriverView->value],
                    Auth::user()
                );
            }
            activity()
                ->performedOn($driver)
                ->causedBy(Auth::user())
                ->log('Driver Created');

            return $driver;
        });
    }

    /**
     * Generate the next driver number
     */
    private function generateDriverNo(): string
    {
        $latestDriver = FleetDriver::withTrashed()->latest('CreatedOn')->first();

        if (!$latestDriver || !$latestDriver->DriverNo) {
            return 'DRV-0001';
        }

        $lastId = (int)str_replace('DRV-', '', $latestDriver->DriverNo);
        $newId = $lastId + 1;

        return 'DRV-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update Driver by ID
     */
    public function update(int $id, array $data): FleetDriver
    {
        return DB::transaction(function () use ($id, $data) {
            $records = FleetDriver::findOrFail($id);

            $records->fill($data);
            $records->ModifiedBy = Auth::id();
            $records->ModifiedOn = now();
            $records->save();

            activity()
                ->performedOn($records)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Fleet Driver Updated');

            return $records;
        });
    }

    /**
     * Soft delete a Fleet Driver by ID
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $records = FleetDriver::findOrFail($id);

            $records->DeletedBy = Auth::id();
            $records->DeletedOn = now();
            $records->save();

            $records->delete(); // Soft delete

            activity()
                ->performedOn($records)
                ->causedBy(Auth::user())
                ->log('Fleet Driver Deleted');

            return true;
        });
    }
}
