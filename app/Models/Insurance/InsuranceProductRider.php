<?php

namespace App\Models\Insurance;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceProductRider extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_InsuranceProductRiders';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InsuranceProviderId',
        'Product',
        'RiderName',
        'Description',
        'AdditionalPremium',
        'CurrencyId',
        'IsOptional',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'InsuranceProductRidersId';
    }

    public function provider()
    {
        return $this->belongsTo(InsuranceProvider::class, 'InsuranceProviderId', 'Id');
    }

    public function product()
    {
        return $this->belongsTo(InsuranceProduct::class, 'Product', 'Id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'CurrencyId', 'Id');
    }
}
