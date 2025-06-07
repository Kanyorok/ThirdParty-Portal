<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetScenarioPlanning extends Model
{
     use UserActorTrait,SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_BudgetScenarioPlanning';

    protected $fillable = [
        
        'scenarioName',
        'description',
        'budgetPeriod',
        'planningMethod',
        'isDefault',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $casts = [
    'IsDefault' => 'boolean',
];

}
