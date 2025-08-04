<?php

namespace App\Services\Insurance;

use App\Models\Insurance\BancassurancePolicy;

class BancassurancePolicyService
{
    /**
     * Create a new class instance.
     */
    public function __construct( public BancassurancePolicy $bancassurancePolicy)
    {
        //
    }
    
    public static function create(
        int $CustomerID,
        int $ProductID,
        ?int $InsurerID,
        string $PolicyNumber,
        float $SumAssured,
        float $PremiumAmount,
        string $PolicyStartDate,
        string $PolicyEndDate,
        int $PaymentFrequency,
        ?int $ReferralID = null,
        ?string $IssuedDate = null,
        ?string $ExpiryDate = null,
        bool $IsActive = true
    ): self {
        $policy = BancassurancePolicy::create([
            'CustomerID' => $CustomerID,
            'ProductID' => $ProductID,
            'InsurerID' => $InsurerID,
            'PolicyNumber' => $PolicyNumber,
            'SumAssured' => $SumAssured,
            'PremiumAmount' => $PremiumAmount,
            'PolicyStartDate' => $PolicyStartDate,
            'PolicyEndDate' => $PolicyEndDate,
            'PaymentFrequency' => $PaymentFrequency,
            'ReferralID' => $ReferralID,
            'IssuedDate' => $IssuedDate,
            'ExpiryDate' => $ExpiryDate,
            'IsActive' => $IsActive
        ]);

        activity()->causedBy(auth()->user()->Id)->performedOn($policy)->event('create')->log("Added Policy {$policy->Id}.");
        return new self($policy);
    }
    
}
