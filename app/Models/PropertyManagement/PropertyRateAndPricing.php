<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\Currency;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyRateAndPricing extends Model
{
    use SoftDeletes, UserActorTrait;
    protected $table = 't_PropertyRateAndPricing';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyId',
        'BlockId',
        'FloorId',
        'UnitId',
        'Rent',
        'ParkingFee',
        'ServiceCharge',
        'OtherCharges',
        'DepositAmount',
        'CurrencyId',
        'TaxId',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
    public static function getPrimaryKey(): string
    {
        return 'PropertyRateAndPricingId';
    }

    Public function property()
    {
        return $this->belongsTo(PropertyRegistry::class, 'PropertyId', 'Id');
    }

    Public function block()
    {
        return $this->belongsTo(PropertyBlock::class, 'BlockId', 'Id');
    }

    Public function floor()
    {
        return $this->belongsTo(PropertyFloor::class, 'FloorId', 'Id');
    }

    Public function unit()
    {
        return $this->belongsTo(PropertyUnit::class, 'UnitId', 'Id');
    }

    Public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }

    Public function tax()
    {
        return $this->belongsTo(FinanceTaxRuleConfiguration::class, 'TaxId', 'Id');
    }
}
