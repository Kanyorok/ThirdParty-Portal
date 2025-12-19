<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Enums\Property\PropertyNewLeaseEnum;
use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Services\Workflow\ApprovalWorkflow;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PropertyLeaseRenewalService
{
    public static function create(
        int    $leaseId,
        int    $paymentFrequencyId,
        string $EndDateCurrentLease,
        string $NewStartDate,
        string $NewEndDate,
        int    $NewMonthlyRent,
        float  $ServiceCharge,
        float  $ParkingFee,
        float  $OtherCharges,
        string $Remarks = null,
        string $Status,
        User   $user,
        UploadedFile $document = null
    ): PropertyLeaseRenewal
    {
        DB::beginTransaction();
        try {
            if (PropertyLeaseRenewal::where('LeaseNumber', $leaseId)->exists()) {
                throw new \Exception('This lease is already renewed.');
            }

            $leaseRenewal = PropertyLeaseRenewal::create([
                'LeaseNumber' => $leaseId,
                'PaymentFrequency' => $paymentFrequencyId,
                'EndDateCurrentLease' => $EndDateCurrentLease,
                'NewStartDate' => $NewStartDate,
                'NewEndDate' => $NewEndDate,
                'NewMonthlyRent' => $NewMonthlyRent,
                'ServiceCharge' => $ServiceCharge,
                'ParkingFee' => $ParkingFee,
                'OtherCharges' => $OtherCharges,
                'Remarks' => $Remarks,
                'Status' => $Status,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            // Update original lease status
            $oldLease = PropertyNewLease::findOrFail($leaseId);
            $oldLease->update(['Status' => PropertyNewLeaseEnum::Renew->value]);

            // Deactivate old schedule
            PropertyLeaseSchedule::where('LeaseNumber', $leaseId)->update(['IsActive' => false]);

            // Submit approval workflow
            $workflow = new ApprovalWorkflow('ApprovalStatus', 'ApprovalStatus');
            $workflow->submit($leaseRenewal, $user, ApprovalEnum::Pending, 'Lease renewal Submitted for Approval');

            // Create new lease schedule
            PropertyLeaseSchedule::create([
                'LeaseNumber' => $leaseId,
                'PaymentFrequency' => $paymentFrequencyId,
                'StartDate' => $NewStartDate,
                'EndDate' => $NewEndDate,
                'BaseRent' => $NewMonthlyRent,
                'ServiceCharge' => $ServiceCharge,
                'ParkingFee' => $ParkingFee,
                'OtherCharges' => $OtherCharges,
                'IsActive' => true,
                'CreatedBy' => $user->Id,
                'ModifiedBy' => $user->Id,
            ]);

            // ================================
            // Generate PDF Offer Letter
            // ================================
            $leaseRenewal->load(['lease', 'lease.tenant']);
            $pdf = Pdf::loadView(
                'property.tenantmanagement.leasemanagement.leaserenewal.renewalofferletter',
                compact('leaseRenewal')
            )->output();

            $leaseRenewal->lease->newDocumentFromContent(
                module: ModulesEnum::Property,
                extension: ExtensionsEnum::Pdf,
                fileName: "Lease_Renewal_Offer_{$leaseRenewal->lease->LeaseNumber}.pdf",
                content: $pdf,
                actor: $user,
                permissions: [PermissionEnum::PropertyLeaseRenewalView->value]
            );

            DB::commit();
            return $leaseRenewal;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function update(
        PropertyLeaseRenewal $leaseRenewal,
        int    $leaseId,
        int    $paymentFrequencyId,
        string $EndDateCurrentLease,
        string $NewStartDate,
        string $NewEndDate,
        int    $NewMonthlyRent,
        float  $ServiceCharge,
        float  $ParkingFee,
        float  $OtherCharges,
        string $Remarks,
        User   $user
    ): void {
        DB::beginTransaction();
        try {
            $leaseRenewal->update([
                'LeaseNumber' => $leaseId,
                'PaymentFrequency' => $paymentFrequencyId,
                'EndDateCurrentLease' => $EndDateCurrentLease,
                'NewStartDate' => $NewStartDate,
                'NewEndDate' => $NewEndDate,
                'NewMonthlyRent' => $NewMonthlyRent,
                'ServiceCharge' => $ServiceCharge,
                'ParkingFee' => $ParkingFee,
                'OtherCharges' => $OtherCharges,
                'Remarks' => $Remarks,
                'ModifiedBy' => $user->Id,
            ]);

            $schedule = PropertyLeaseSchedule::where('LeaseNumber', $leaseId)->latest()->first();
            if ($schedule) {
                $schedule->update([
                    'PaymentFrequency' => $paymentFrequencyId,
                    'StartDate' => $NewStartDate,
                    'EndDate' => $NewEndDate,
                    'BaseRent' => $NewMonthlyRent,
                    'ServiceCharge' => $ServiceCharge,
                    'ParkingFee' => $ParkingFee,
                    'OtherCharges' => $OtherCharges,
                    'ModifiedBy' => $user->Id,
                    'IsActive' => true,
                ]);
            }

            // Regenerate PDF Offer Letter after update
            $leaseRenewal->load(['lease', 'lease.tenant']);
            $pdf = Pdf::loadView(
                'property.tenantmanagement.leasemanagement.leaserenewal.renewalofferletter',
                compact('leaseRenewal')
            )->output();

            $leaseRenewal->lease->newDocumentFromContent(
                module: ModulesEnum::Property,
                extension: ExtensionsEnum::Pdf,
                fileName: "Lease_Renewal_Offer_{$leaseRenewal->lease->LeaseNumber}.pdf",
                content: $pdf,
                actor: $user,
                permissions: [PermissionEnum::PropertyLeaseRenewalView->value]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function delete(PropertyLeaseRenewal $leaseRenewal, User $user): void
    {
        DB::beginTransaction();
        try {
            $leaseId = $leaseRenewal->LeaseNumber;

            PropertyLeaseSchedule::where('LeaseNumber', $leaseId)->get()->each(function ($schedule) use ($user) {
                $schedule->DeletedBy = $user->Id;
                $schedule->save();
                $schedule->delete();
            });

            $leaseRenewal->DeletedBy = $user->Id;
            $leaseRenewal->save();
            $leaseRenewal->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
