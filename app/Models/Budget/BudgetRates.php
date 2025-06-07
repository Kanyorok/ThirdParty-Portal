<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

class BudgetRates extends Model
{
     use UserActorTrait,SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_BudgetRates';

    protected $fillable = [
        
        'RateTypeCode',
        'RateTypeName',
        'Description',
        'IsDefault',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $cast=[
        'IsDefault' => 'boolean',
    ];

}
