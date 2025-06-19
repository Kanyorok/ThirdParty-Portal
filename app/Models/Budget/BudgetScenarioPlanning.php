<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetScenarioPlanning extends Model
{
    use UserActorTrait, SoftDeletes;


    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetScenarioPlanningId';
    }

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

    public function planningMethodRef(){

        return $this->belongsTo(BudgetPlanningMethods::class , 'planningMethod', 'Id');
    }

    public function budgetPeriodRef(){

        return $this->belongsTo(BudgetPeriods::class , 'budgetPeriod', 'Id');
    }

    protected $casts = [
    'isDefault' => 'boolean',
];
 
}
