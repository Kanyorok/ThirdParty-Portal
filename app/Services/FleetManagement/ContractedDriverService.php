<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\ContractedDriver;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use Illuminate\Http\UploadedFile;

class ContractedDriverService
{
    /**
     * Create a new Inspection
     */

    public function create(array $data, UploadedFile $document = null): ContractedDriver
    {
        return DB::transaction(function () use ($data, $document) {
            $data['DriverNo'] = $this->generateDriverNo();
            $data['FullName'] = $data['FullName'] ?? null;
            $data['NationalID'] = $data['NationalID'] ?? null;
            $data['Phone'] = $data['Phone'] ?? null;
            $data['CompanyID'] = $data['CompanyID'] ?? null;
            $data['ContractStartDate'] = $data['ContractStartDate'] ?? null;
            $data['ContractEndDate'] = $data['ContractEndDate'] ?? null;
            $data['Notes'] = $data['Notes'] ?? null;
            $data['IsActive'] = $data['IsActive'] ?? 1;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();


            $drivers = ContractedDriver::create($data);

            if ($document) {
                $drivers->newDocument(
                    ModulesEnum::Fleet,
                    $document,
                    [PermissionEnum::ContractedDriverView->value],
                    Auth::user()
                );
            }
            activity()
                ->performedOn($drivers)
                ->causedBy(Auth::user())
                ->log('Contracted Driver Created');

            return $drivers;
        });
    }

    private function generateDriverNo(): string
    {
        $latestDriver = ContractedDriver::withTrashed()->latest('CreatedOn')->first();

        if (!$latestDriver || !$latestDriver->DriverNo) {
            return 'DRV-0001';
        }

        $lastId = (int)str_replace('DRV-', '', $latestDriver->DriverNo);
        $newId = $lastId + 1;

        return 'DRV-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }


    /**
     * Update Inspection
     */
    public function update(ContractedDriver $drivers, array $data): ContractedDriver
    {
        return DB::transaction(function () use ($drivers, $data) {

            $drivers->fill($data);
            $drivers->ModifiedBy = Auth::id();
            $drivers->ModifiedOn = now();
            $drivers->save();

            activity()
                ->performedOn($drivers)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Driver Updated');

            return $drivers;
        });
    }

    /**
     * Soft delete a Fleet Vehicle
     */
    public function delete(ContractedDriver $drivers): bool
    {
        return DB::transaction(function () use ($drivers) {

            $drivers->DeletedBy = Auth::id();
            $drivers->DeletedOn = now();
            $drivers->save();

            $drivers->delete(); // Soft delete

            activity()
                ->performedOn($drivers)
                ->causedBy(Auth::user())
                ->log('Driver Updated');

            return true;
        });
    }
}
