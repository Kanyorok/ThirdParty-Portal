<?php

namespace App\Services\Insurance;


use App\Models\Auth\User;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\BancassuranceCustomer;
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
        ?BancAssuranceReferral $ReferralID = null,
        string                 $FullName,
        string                 $NationalID,
        string                 $KRAPIN,
        DateTime               $DateOfBirth,
        CodeDetail             $Gender,
        CodeDetail             $MaritalStatus,
        string                 $PhoneNumber,
        string                 $Email,
        string                 $Address,
        CodeDetail             $Occupation,
        User                   $user
    ): self
    {
        $customer = BancassuranceCustomer::create([
            'ReferralID' => $ReferralID->Id ?? null,
            'FullName' => $FullName,
            'NationalID' => $NationalID,
            'KRAPIN' => $KRAPIN,
            'DateOfBirth' => $DateOfBirth,
            'Gender' => $Gender->ID,
            'MaritalStatus' => $MaritalStatus->ID,
            'PhoneNumber' => $PhoneNumber,
            'Email' => $Email,
            'Address' => $Address,
            'Occupation' => $Occupation->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($customer)->event('create')->log("Added Customer {$customer->Id}.");
        return new self($customer);
    }
}

