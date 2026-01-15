<?php

namespace App\Services\Insurance;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Insurance\BancassuranceCommissionRule;
use App\Models\Insurance\InsuranceProduct;

class CommissionRuleService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceCommissionRule $rule) {}

    public static function create(
        string           $RuleName,
        InsuranceProduct $ProductId,
        ?CodeDetail $PolicyTypeId = null,
        float $CommissionRate,
        float $FixedAmount,
        Currency $CurrencyId = null,
        ?CodeDetail $AppliesTo = null,
        ?bool $IsActive = null,
        User   $user
    ): self {

        $rule = BancassuranceCommissionRule::create([
            'RuleName' => $RuleName,
            'ProductId' => $ProductId->Id,
            'PolicyTypeId' => $PolicyTypeId->ID ?? null,
            'CommissionRate' => $CommissionRate,
            'FixedAmount' => $FixedAmount,
            'CurrencyId' => $CurrencyId->Id,
            'AppliesTo' => $AppliesTo->ID ?? null,
            'IsActive' => $IsActive ? 1 : 0 ?? null,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        activity()->causedBy($user->Id)->performedOn($rule)->event('create')->log("Added Provider {$rule->Id}.");
        return new self($rule);
    }
}
