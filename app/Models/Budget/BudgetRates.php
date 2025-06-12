<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetRates extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetRatesId';
    }

    protected $table = 't_BudgetRates';

    protected $fillable = [

        'RateTypeCode',
        'RateTypeName',
        'Description',
        'IsDefault',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $cast = [
        'IsDefault' => 'boolean',
    ];

}
