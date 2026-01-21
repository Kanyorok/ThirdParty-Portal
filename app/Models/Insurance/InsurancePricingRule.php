<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsurancePricingRule extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_InsurancePricingRules';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InsuranceProviderId',
        'Product',
        'RuleName',
        'CoverageAmountMax',
        'CoverageAmountMin',
        'PremiumRate',
        'CurrencyId',
        'AgeMin',
        'AgeMax',
        'TenureMin',
        'TenureMax',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'InsurancePricingRulesId';
    }

    public function provider()
    {
        return $this->belongsTo(InsuranceProvider::class, 'InsuranceProviderId', 'Id');
    }

    public function product()
    {
        return $this->belongsTo(InsuranceProduct::class, 'Product', 'Id');
    }
}
