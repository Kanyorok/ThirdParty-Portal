<?php

namespace App\Services\Insurance\ProviderAndProducts;

use App\Models\Insurance\InsuranceProductRider;
use App\Models\Auth\User;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;

class InsuranceProductRiderService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public InsuranceProductRider $rider)
    {
    }

    public static function create(
        InsuranceProvider $InsuranceProviderId,
        InsuranceProduct  $Product,
        string            $RiderName,
        ?string           $Description = null,
        float             $AdditionalPremium,
        ?bool             $IsOptional = null,
        ?bool             $IsActive = null,
        User              $user
    ): self
    {

        $rider = InsuranceProductRider::create([
            'InsuranceProviderId' => $InsuranceProviderId->Id,
            'Product' => $Product->Id,
            'RiderName' => $RiderName,
            'Description' => $Description ?? null,
            'AdditionalPremium' => $AdditionalPremium,
            'IsOptional' => $IsOptional ? 1 : 0 ?? null,
            'IsActive' => $IsActive ? 1 : 0 ?? null,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($rider)->event('create')->log("Added Provider {$rider->Id}.");
        return new self($rider);
    }
}
