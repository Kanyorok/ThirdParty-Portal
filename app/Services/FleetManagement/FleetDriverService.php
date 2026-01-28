<?php

namespace App\Services\FleetManagement;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Fleet\FleetDriver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetDriverService
{
    /**
     * Create a new Driver with default status (NO WORKFLOW)
     */
    public function create(array $data, UploadedFile $document = null): FleetDriver
    {
        return DB::transaction(function () use ($data, $document) {
            $data['DriverNo'] = $this->generateDriverNo();
            $data['IsActive'] = $data['IsActive'] ?? 1;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();

            if (empty($data['DriverStatus'])) {
                $defaultStatusValue = $this->getDefaultStatusValue();
                $statusId = $this->getStatusIdByValue($defaultStatusValue);
                $data['DriverStatus'] = $statusId;
            }

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
     * Update Driver by ID - NO WORKFLOW LOGGING
     */
    public function update(int $id, array $data, UploadedFile $document = null): FleetDriver
    {
        return DB::transaction(function () use ($id, $data, $document) {
            $driver = FleetDriver::findOrFail($id);

            $driver->fill($data);
            $driver->ModifiedBy = Auth::id();
            $driver->ModifiedOn = now();
            $driver->save();

            if ($document) {
                foreach ($driver->documents as $doc) {
                    $doc->delete();
                }

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
                ->withProperties(['attributes' => $data])
                ->log('Fleet Driver Updated');

            return $driver;
        });
    }

    /**
     * Soft delete a Fleet Driver by ID - NO WORKFLOW LOGGING
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $driver = FleetDriver::findOrFail($id);

            $driver->DeletedBy = Auth::id();
            $driver->DeletedOn = now();
            $driver->save();

            $driver->delete();

            // Removed workflow logging
            activity()
                ->performedOn($driver)
                ->causedBy(Auth::user())
                ->log('Fleet Driver Deleted');

            return true;
        });
    }

    /**
     * Generate the next driver number
     */
    private function generateDriverNo(): string
    {
        $latestDriver = FleetDriver::withTrashed()->latest('CreatedOn')->first();

        if (! $latestDriver || ! $latestDriver->DriverNo) {
            return 'DRV-0001';
        }

        $lastId = (int)str_replace('DRV-', '', $latestDriver->DriverNo);
        $newId = $lastId + 1;

        return 'DRV-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get CodeDetail ID by Value (DriverAvailabilityStatus)
     */
    private function getStatusIdByValue(?string $value): ?int
    {
        if (! $value) {
            return null;
        }

        return CodeDetail::where('CodeID', 'DriverAvailabilityStatus')
            ->where('Value', $value)
            ->value('Id');
    }

    /**
     * Get CodeDetail Value by ID (DriverAvailabilityStatus)
     */
    private function getStatusValueById(?int $id): ?string
    {
        if (! $id) {
            return null;
        }

        return CodeDetail::where('CodeID', 'DriverAvailabilityStatus')
            ->where('Id', $id)
            ->value('Value');
    }

    /**
     * Get the default status value (DriverAvailabilityStatus)
     */
    private function getDefaultStatusValue(): ?string
    {
        return CodeDetail::where('CodeID', 'DriverAvailabilityStatus')
            ->where('Description', 'Available')
            ->value('Value');
    }
}
