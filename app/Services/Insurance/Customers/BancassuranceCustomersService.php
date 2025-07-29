<?php

namespace App\Services\Insurance\Customers;

class BancassuranceCustomersService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    
    public static function create(
        PropertyRegistry $ReferralID,
        string $BlockName,
        string $Description = null,
        User   $user
    ): self
    {
        $customers = BancassuranceCustomers::create([
        'ReferralID' => $ReferralID->Id,
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

        activity()->causedBy($user->Id)->performedOn($block)->event('create')->log("Added Property Block {$block->Id}.");
        return new self($block);
    }
}

