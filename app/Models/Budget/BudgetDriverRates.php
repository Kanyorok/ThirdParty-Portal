<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetDriverRates extends Model
{
    use UserActorTrait,SoftDeletes;

    protected $table='t_BudgetDriverRates';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetDriverRatesId';
    }

    protected $fillable = [
        'PeriodTypeID',
        'ProductTypeID',
        'RateTypeID',
        'RateValue',
        'EffectiveDate',
        'Source',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn'   => 'datetime',
        'ModifiedOn'  => 'datetime',
        'DeletedOn'   => 'datetime',
    ];

    //Relations
    public function productType(): BelongsTo
    {
        return $this->belongsTo(BudgetProductType::class,'ProductTypeID','Id');
    }

    public function periodType():BelongsTo
    {
        return $this->belongsTo(BudgetPeriodTypes::class,'PeriodTypeID','Id');
    }

    public function rateType():BelongsTo
    {
        return $this->belongsTo(BudgetRates::class,'RateTypeID','Id');
    }
    
}
