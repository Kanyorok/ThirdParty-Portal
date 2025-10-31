<?php

namespace App\Services\Insurance;


use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\ThirdParty\ThirdParties;
use DateTime;

class BancassuranceCustomersService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceCustomer $customer)
    {
    }

    public static function create(
        ThirdParties $ThirdPartyId,
        ?BancAssuranceReferral $ReferralID = null,
        DateTime              $DateOfBirth,
        CodeDetail            $Gender,
        CodeDetail            $MaritalStatus,
        CodeDetail            $Occupation,
        User                  $user
    ): self
    {
        $customer = BancassuranceCustomer::create([
            'ThirdPartyId' => $ThirdPartyId->Id,
            'ReferralID' => $ReferralID->Id ?? null,
            'DateOfBirth' => $DateOfBirth,
            'Gender' => $Gender->ID,
            'MaritalStatus' => $MaritalStatus->ID,
            'Occupation' => $Occupation->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($customer)->event('create')->log("Added Customer {$customer->Id}.");
        return new self($customer);
    }
}

