<?php

namespace App\Services\Property\TenantAndLease;

use App\Models\Auth\User;
use App\Models\PropertyManagement\PropertyBlock;
use App\Models\PropertyManagement\PropertyFloor;
use App\Models\PropertyManagement\PropertyInterest;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\PropertyManagement\PropertyUnit;
use App\Models\Core\Approval\CodeDetail;
use Illuminate\Support\Carbon;

class PropertyInterestService
{
    public function create(
        PropertyRegistry    $PropertyId,
        PropertyBlock   $BlockId,
        PropertyFloor   $FloorId,
        PropertyUnit    $UnitId,
        PropertyNewTenant    $TenantId,
        Carbon   $InterestedStartDate,
        Carbon  $InterestedEndDate,
        CodeDetail  $PaymentFrequency,
        string  $AdditionalInformation,
        User    $user
    ): PropertyInterest {

        return PropertyInterest::create([
            'PropertyId' => $PropertyId->Id,
            'BlockId' => $BlockId->Id,
            'FloorId' => $FloorId->Id,
            'UnitId' => $UnitId->Id,
            'TenantId' => $TenantId->Id,
            'InterestedStartDate' => $InterestedStartDate,
            'InterestedEndDate' => $InterestedEndDate,
            'PaymentFrequency' => $PaymentFrequency->ID,
            'AdditionalInformation' => $AdditionalInformation ?? null,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);
    }


    public function update(
    PropertyInterest $interest,
    PropertyRegistry $PropertyId,
    PropertyBlock $BlockId,
    PropertyFloor $FloorId,
    PropertyUnit $UnitId,
    PropertyNewTenant $TenantId,
    Carbon $InterestedStartDate,
    Carbon $InterestedEndDate,
    CodeDetail $PaymentFrequency,
    ?string $AdditionalInformation,
    User $user
    ): PropertyInterest {

        $interest->update([
            'PropertyId' => $PropertyId->Id,
            'BlockId' => $BlockId->Id,
            'FloorId' => $FloorId->Id,
            'UnitId' => $UnitId->Id,
            'TenantId' => $TenantId->Id,
            'InterestedStartDate' => $InterestedStartDate,
            'InterestedEndDate' => $InterestedEndDate,
            'PaymentFrequency' => $PaymentFrequency->ID,
            'AdditionalInformation' => $AdditionalInformation,
            'ModifiedBy' => $user->Id,
        ]);

        return $interest;
    }

}
