<?php

namespace App\Services\Insurance\ProviderAndProducts;

use App\Models\Auth\User;
use App\Models\Insurance\InsuranceProduct;
use App\Models\Insurance\InsuranceProvider;
use App\Models\Insurance\InsurancePricingRule;

class InsurancePricingRuleService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public InsurancePricingRule $rider)
    {
    }
    public static function create(
        InsuranceProvider $InsuranceProviderId,
        InsuranceProduct $Product,
        string $RuleName,
        float $CoverageAmountMin,
        float $CoverageAmountMax,
        float $PremiumRate,
        int $AgeMin,
        int $AgeMax,
        int $TenureMin,
        int $TenureMax,
        bool $IsActive,
        User   $user
    ) : self {
         
        $ruler = InsurancePricingRule::create([
        'InsuranceProviderId' => $InsuranceProviderId->Id,
        'Product'=> $Product->Id,
        'RuleName' => $RuleName,
        'CoverageAmountMin' => $CoverageAmountMin,
        'CoverageAmountMax' => $CoverageAmountMax,
        'PremiumRate' => $PremiumRate,
        'AgeMin' => $AgeMin,
        'AgeMax' => $AgeMax,
        'TenureMin' => $TenureMin,
        'TenureMax' => $TenureMax,
        'IsActive' => $IsActive ? 1 : 0,
        'CreatedBy' => $user->Id,
        'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($ruler)->event('create')->log("Added Provider {$ruler->Id}.");
        return new self($ruler);
    }

}