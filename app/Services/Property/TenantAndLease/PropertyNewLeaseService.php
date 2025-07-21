<?php

namespace App\Services\Property\TenantAndLease;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Services\Property\TenantAndLease\PropertyLeaseScheduleService;
use DateTime;
use Illuminate\Http\UploadedFile;

class PropertyNewLeaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(PropertyNewLease $propertyNewLease)
    {
    }

    public static function create(
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
        int $DueDay,
        string $SpecialTerms,
        User $user,
        UploadedFile $document = null
    ): self {

            $lastLeaseNumber = PropertyNewLease::withTrashed() // in case you're using soft deletes
                ->selectRaw("MAX(CAST(SUBSTRING(LeaseNumber, 7, LEN(LeaseNumber)) AS INT)) as max_number")
                ->value('max_number');

            $nextNumber = $lastLeaseNumber ? $lastLeaseNumber + 1 : 1;
            $leaseNumber = 'LEASE-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);


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

        return new self($newlease);
    }




    //Update
    public static function update(
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

        return new self($lease);
    }


}
