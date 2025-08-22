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
        InsuranceProduct $Product,
        string $RiderName,
        string $Description,  
        float  $AdditionalPremium,    
        bool $IsOptional,
        bool $IsActive,
        User   $user
    ) : self {
         
        $rider = InsuranceProductRider::create([
        'InsuranceProviderId' => $InsuranceProviderId->Id,
        'Product'=> $Product->Id,
        'RiderName' => $RiderName,
        'Description' => $Description,
        'AdditionalPremium' => $AdditionalPremium,        
        'IsOptional' => $IsOptional ? 1 : 0,
        'IsActive' => $IsActive ? 1 : 0,
        'CreatedBy' => $user->Id,
        'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($rider)->event('create')->log("Added Provider {$rider->Id}.");
        return new self($rider);
    }
}
