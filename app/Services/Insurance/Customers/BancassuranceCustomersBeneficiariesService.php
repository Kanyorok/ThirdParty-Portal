<?php

namespace App\Services\Insurance\Customers;

use App\Models\Auth\User;
use App\Models\Insurance\BancassuranceBeneficiaries;
use App\Models\Insurance\BancassuranceCustomers;
use App\Models\Insurance\BancassurancePolicies;
use App\Models\Core\CodeDetail;


class BancassuranceCustomersBeneficiariesService
{
    /**
     * Create a new class instance.
     */
    public static function create(
        BancassuranceCustomers $CustomerID,
        BancassurancePolicies  $PolicyID ,     
        string $FullName,
        CodeDetail $Relationship,
        string $IDNumber,
        string  $Phone,
        string $Email,
        float $PercentageShare,
        bool $IsPrimary,
        User   $user,
    ): self
    {
        $customer = BancassuranceBeneficiaries::create([
        'CustomerID' => $CustomerID->Id,
        'PolicyID' => $PolicyID->Id,
        'FullName' => $FullName,
        'Relationship' => $Relationship->ID,
        'IDNumber' => $IDNumber,
        'Phone' => $Phone,
        'Email' => $Email,
        'PercentageShare' => $PercentageShare,
        'IsPrimary' => $IsPrimary ? 1 : 0,
        'CreatedBy' => $user->Id,
        'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($customer)->event('create')->log("Added Customer Contacts {$customer->Id}.");
        return new self($customer);
    }
}