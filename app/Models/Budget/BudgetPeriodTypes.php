<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class BudgetPeriodTypes extends Model
{
     use UserActorTrait,SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
