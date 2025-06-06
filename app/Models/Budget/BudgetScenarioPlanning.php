<?php

namespace App\Models\Budget;

use Illuminate\Database\Eloquent\Model;

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
