<?php

namespace App\Services\FleetManagement;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Fleet\FleetInsuranceTracker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FleetInsuranceTrackerService
{
    /**
     * Create a new Fleet Vehicle
     */
    public function create(array $data, UploadedFile $document = null): FleetInsuranceTracker
    {
        return DB::transaction(function () use ($data, $document) {
            $data['InsuranceNo'] = $this->generateInsuranceNo();
            $data['VehicleID'] = $data['VehicleID'] ?? null;
            $data['InsuranceProvider'] = $data['InsuranceProvider'] ?? null;
            $data['PolicyNumber'] = $data['PolicyNumber'] ?? null;
            $data['CoverageStartDate'] = $data['CoverageStartDate'] ?? null;
            $data['PremiumAmount'] = $data['PremiumAmount'] ?? null;
            $data['RenewalReminderDate'] = $data['RenewalReminderDate'] ?? null;
            $data['Notes'] = $data['Notes'] ?? null;
            $data['Status'] = $data['Status'] ?? null;
            $data['CreatedBy'] = Auth::id();
            $data['CreatedOn'] = now();


            $insurance = FleetInsuranceTracker::create($data);

            // If you still want to attach via newDocument service
            if ($document) {
                $insurance->newDocument(
                    ModulesEnum::Fleet,
                    $document,
                    [PermissionEnum::FleetInsuranceTrackerView->value],
                    Auth::user()
                );
            }

            // Activity log
            activity()
                ->performedOn($insurance)
                ->causedBy(Auth::user())
                ->log('Insurance Created');

            return $insurance;
        });
    }

    private function generateInsuranceNo(): string
    {
        $latestInsurance = FleetInsuranceTracker::withTrashed()->latest('CreatedOn')->first();

        if (! $latestInsurance || ! $latestInsurance->InsuranceNo) {
            return 'INS-0001';
        }

        $lastId = (int)str_replace('INS-', '', $latestInsurance->InsuranceNo);
        $newId = $lastId + 1;

        return 'INS-' . str_pad($newId, 4, '0', STR_PAD_LEFT);
    }

    public function update(FleetInsuranceTracker $records, array $data): FleetInsuranceTracker
    {
        return DB::transaction(function () use ($records, $data) {

            // Fill other attributes
            $records->fill($data);

            // ✅ Explicitly ensure PolicyNumber is updated
            if (isset($data['PolicyNumber'])) {
                $records->PolicyNumber = $data['PolicyNumber'];
            }

            $records->ModifiedBy = Auth::id();
            $records->ModifiedOn = now();
            $records->save();

            activity()
                ->performedOn($records)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Fleet Insurance Updated');

            return $records;
        });
    }

    /**
     * Soft delete a Fleet Vehicle
     */
    public function delete(FleetInsuranceTracker $records): bool
    {
        return DB::transaction(function () use ($records) {

            $records->DeletedBy = Auth::id();
            $records->DeletedOn = now();
            $records->save();

            $records->delete(); // Soft delete

            activity()
                ->performedOn($records)
                ->causedBy(Auth::user())
                ->log('Fleet Insurance Deleted');

            return true;
        });
    }
}
