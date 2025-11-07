<?php

namespace App\Services\FleetManagement;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetDriver;
use App\Models\Fleet\FleetDriverLicenseTracking;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FleetDriverLicenseTrackingService
{
    /**
     * Create a new License
     */
    public function create(array $data): FleetDriverLicenseTracking
    {
        return DB::transaction(function () use ($data) {
            $license = FleetDriverLicenseTracking::create([
                'DriverID' => $data['DriverID'] ?? null,
                'LicenseNumber' => $data['LicenseNumber'] ?? null,
                'LicenseCategory' => $data['LicenseCategory'] ?? null,
                'IssueDate' => $data['IssueDate'] ?? null,
                'ExpiryDate' => $data['ExpiryDate'] ?? null,
                'RenewalDate' => $data['RenewalDate'] ?? null,
                'Notes' => $data['Notes'] ?? null,
                'CreatedBy' => $data['CreatedBy'] = Auth::id(),
                'CreatedOn' => $data['CreatedOn'] = now()
            ]);

            activity()
                ->performedOn($license)
                ->causedBy(Auth::user())
                ->log(' Driver License Created');

            return $license;
        });
    }

    /**
     * Update License
     */
    public function update(FleetDriverLicenseTracking $license, array $data): FleetDriverLicenseTracking
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
                ->log(' Driver License Updated');

            return $license;
        });
    }

    /**
     * Soft delete a License
     */
    public function delete(FleetDriverLicenseTracking $license): bool
    {
        return DB::transaction(function () use ($license) {
            $license->DeletedBy = Auth::id();
            $license->DeletedOn = now();
            $license->save();

            $license->delete(); // Soft delete

            activity()
                ->performedOn($license)
                ->causedBy(Auth::user())
                ->log(' Driver License Deleted');

            return true;
        });
    }
}
