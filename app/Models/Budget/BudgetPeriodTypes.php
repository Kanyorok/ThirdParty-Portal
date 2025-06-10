<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPeriodTypes extends Model
{
     use UserActorTrait,SoftDeletes;

    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetPeriodTypesId';
    }


    protected $table = 't_BudgetPeriodTypes';

    protected $fillable = [
        
        'PeriodType',
        'Code',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $cast=[
        'IsActive' => 'boolean',
    ];

}
