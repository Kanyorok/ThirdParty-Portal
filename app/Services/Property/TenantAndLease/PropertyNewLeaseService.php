<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Core\ApprovalEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Workflow\ApprovalWorkflow;
use DateTime;
use Illuminate\Http\UploadedFile;

class PropertyNewLeaseService
{
    /**
     * Create a new class instance.
     */
    protected ApprovalWorkflow $workflow;

    public function __construct(PropertyNewLease $propertyNewLease, ApprovalWorkflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function create(
        PropertyNewTenant $Tenant,
        PropertyRegistry $PropertyID,
        PropertyBlock $BlockID,
        PropertyFloor $FloorID,
        PropertyUnit $Unit,
        DateTime $StartDate,
        DateTime $EndDate,
        CodeDetail $PaymentFrequency,
        float $MonthlyRent,
        float $Deposit,
        float $ServiceCharge,
        float $ParkingFee,
        float $OtherCharges,
        string $Status,
        string $ApprovalStatus,
        bool $IsOfferGenerated,
        int $DueDay,
        string $SpecialTerms = null,
        User $user,
        Currency $CurrencyId,
        FinanceTaxRuleConfiguration $TaxId,
        UploadedFile $document = null
    ): self {

        $lastLeaseNumber = PropertyNewLease::withTrashed()
            ->selectRaw("CAST(SUBSTRING(LeaseNumber, 7, 5) AS INT) as num")
            ->orderByDesc('num')
            ->value('num');

        $nextNumber = $lastLeaseNumber ? $lastLeaseNumber + 1 : 1;
        $leaseNumber = 'LEASE-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);


        $Unit = PropertyUnit::findOrFail($Unit->Id);
        $Unit->update([
            'IsRentable' => 0,
            'CurrentStatus' => 0,
        ]);


        $newlease = PropertyNewLease::create([
            'LeaseNumber' => $leaseNumber,
            'Tenant' => $Tenant->Id,
            'PropertyID' => $PropertyID->Id,
            'BlockID' => $BlockID->Id,
            'FloorID' => $FloorID->Id,
            'Unit' => $Unit->Id,
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
            'PaymentFrequency' => $PaymentFrequency->ID,
            'MonthlyRent' => $MonthlyRent,
            'Deposit' => $Deposit,
            'DueDay' => $DueDay,
            'ServiceCharge' => $ServiceCharge,
            'ParkingFee' => $ParkingFee,
            'OtherCharges' => $OtherCharges,
            'SpecialTerms' => $SpecialTerms,
            'Status' => $Status,
            'ApprovalStatus' => $ApprovalStatus,
            'IsOfferGenerated' => $IsOfferGenerated,
            'CurrencyId' => $CurrencyId->Id,
            'TaxId' => $TaxId->Id,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document) {
            $newlease->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyNewLeaseView->value],
                $user
            );
        }

            
        //create workflow instance and submit for approval
        $leaseWorkflow = new ApprovalWorkflow('ApprovalStatus',  'ApprovalStatus' );
        $leaseWorkflow->submit(
            $newlease,
            $user,
            ApprovalEnum::Pending,
            'Lease Submitted for Approval'
        );
            
           
        PropertyLeaseScheduleService::create(
            leaseId: $newlease->Id,
            paymentFrequencyId: $PaymentFrequency->ID,
            startDate: $StartDate->format('Y-m-d'),
            endDate: $EndDate->format('Y-m-d'),
            baseRent: $MonthlyRent,
            serviceCharge: $ServiceCharge,
            parkingFee: $ParkingFee,
            otherCharges: $OtherCharges,
            user: $user
        );

        activity()->causedBy($user->Id)
            ->performedOn($newlease)
            ->event('create')
            ->log("Added New Lease {$newlease->Id}.");

        return new self($newlease, $this->workflow);
    }


    //Update
    public function update(
        PropertyNewLease $lease,
        PropertyRegistry $PropertyID,
        PropertyBlock $BlockID,
        PropertyFloor $FloorID,
        PropertyUnit $Unit,
        DateTime $StartDate,
        DateTime $EndDate,
        CodeDetail $PaymentFrequency,
        float $MonthlyRent,
        float $Deposit,
        float $ServiceCharge,
        float $ParkingFee,
        float $OtherCharges,
        int $DueDay,
        string $SpecialTerms,
        User $user,
        Currency $CurrencyId,
        FinanceTaxRuleConfiguration $TaxId,
        UploadedFile $document = null
    ): self {
        $lease->update([
            'PropertyID' => $PropertyID->Id,
            'BlockID' => $BlockID->Id,
            'FloorID' => $FloorID->Id,
            'Unit' => $Unit->Id,
            'StartDate' => $StartDate,
            'EndDate' => $EndDate,
            'PaymentFrequency' => $PaymentFrequency->ID,
            'MonthlyRent' => $MonthlyRent,
            'Deposit' => $Deposit,
            'DueDay' => $DueDay,
            'ServiceCharge' => $ServiceCharge,
            'ParkingFee' => $ParkingFee,
            'OtherCharges' => $OtherCharges,
            'SpecialTerms' => $SpecialTerms,
            'CurrencyId' => $CurrencyId->Id,
            'TaxId' => $TaxId->Id,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document) {
            $lease->newDocument(
                ModulesEnum::Property,
                $document,
                [PermissionEnum::PropertyNewLeaseView->value],
                $user
            );
        }

        //Delete old schedule entries if needed (optional cleanup)
        PropertyLeaseSchedule::where('LeaseNumber', $lease->Id)->delete();

        //Regenerate schedule
        PropertyLeaseScheduleService::create(
            leaseId: $lease->Id,
            paymentFrequencyId: $PaymentFrequency->ID,
            startDate: $StartDate->format('Y-m-d'),
            endDate: $EndDate->format('Y-m-d'),
            baseRent: $MonthlyRent,
            serviceCharge: $ServiceCharge,
            parkingFee: $ParkingFee,
            otherCharges: $OtherCharges,
            user: $user
        );

        activity()->causedBy($user->Id)
            ->performedOn($lease)
            ->event('update')
            ->log("Updated Lease {$lease->Id}.");

        return new self($lease, $this->workflow);
    }


}
