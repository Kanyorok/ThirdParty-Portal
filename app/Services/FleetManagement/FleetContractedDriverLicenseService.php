<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\ContractedDriver;
use App\Models\Fleet\FleetContractedDriverLicense;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FleetContractedDriverLicenseService
{
    /**
     * Create a new License
     */
    public function create(array $data): FleetContractedDriverLicense
    {
        return DB::transaction(function () use ($data) {
            $license = FleetContractedDriverLicense::create([
                'ContractedDriverID' => $data['ContractedDriverID'] ?? null,
                'LicenseNumber'      => $data['LicenseNumber'] ?? null,
                'LicenseCategory'    => $data['LicenseCategory'] ?? null,
                'IssueDate'          => $data['IssueDate'] ?? null,
                'ExpiryDate'         => $data['ExpiryDate'] ?? null,
                'Notes'              => $data['Notes'] ?? null,
                'CreatedBy'        =>  $data['CreatedBy'] = Auth::id(),
                'CreatedOn'        =>  $data['CreatedOn'] = now()
            ]);

            activity()
                ->performedOn($license)
                ->causedBy(Auth::user())
                ->log('Contracted Driver License Created');

            return $license;
        });
    }

    /**
     * Update License
     */
    public function update(FleetContractedDriverLicense $license, array $data): FleetContractedDriverLicense
    {
        return DB::transaction(function () use ($license, $data) {
            $license->fill($data);
            $license->ModifiedBy = Auth::id();
            $license->ModifiedOn = now();
            $license->save();

            activity()
                ->performedOn($license)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Contracted Driver License Updated');

            return $license;
        });
    }

    /**
     * Soft delete a License
     */
    public function delete(FleetContractedDriverLicense $license): bool
    {
        return DB::transaction(function () use ($license) {
            $license->DeletedBy = Auth::id();
            $license->DeletedOn = now();
            $license->save();

            $license->delete(); // Soft delete

            activity()
                ->performedOn($license)
                ->causedBy(Auth::user())
                ->log('Contracted Driver License Deleted');

            return true;
        });
    }
}
