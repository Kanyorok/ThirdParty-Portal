<?php

namespace App\Services\Insurance;

use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceCustomers;
use App\Models\Insurance\BancassurancePolicy;
use App\Models\Insurance\BancAssuranceReferral;
use Date;

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
        BancassuranceCustomers $CustomerID,
        CodeDetail $ProductID,
        ?CodeDetail $InsurerID,
        string $PolicyNumber,
        float $SumAssured,
        float $PremiumAmount,
        Date $PolicyStartDate,
        Date $PolicyEndDate,
        CodeDetail $PaymentFrequency,
        ?BancAssuranceReferral $ReferralID = null,
        ?Date $IssuedDate = null,
        ?Date $ExpiryDate = null,
        bool $IsActive = true
    ): self {
        $policy = BancassurancePolicy::create([
            'CustomerID' => $CustomerID -> Id,
            'ProductID' => $ProductID -> ID,
            'InsurerID' => $InsurerID -> ID ?? null,
            'PolicyNumber' => $PolicyNumber,
            'SumAssured' => $SumAssured,
            'PremiumAmount' => $PremiumAmount,
            'PolicyStartDate' => $PolicyStartDate,
            'PolicyEndDate' => $PolicyEndDate,
            'PaymentFrequency' => $PaymentFrequency,
            'ReferralID' => $ReferralID -> Id ?? null,
            'IssuedDate' => $IssuedDate,
            'ExpiryDate' => $ExpiryDate,
            'IsActive' => $IsActive
        ]);

        activity()->causedBy(auth()->user()->Id)->performedOn($policy)->event('create')->log("Added Policy {$policy->Id}.");
        return new self($policy);
    }
    
}
