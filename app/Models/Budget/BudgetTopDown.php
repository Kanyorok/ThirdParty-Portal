<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetTopDown extends Model
{
    public static function getPrimaryKey(): string
    {
        return 'BudgetTopDownId';
    }
     use SoftDeletes;
     use UserActorTrait;

    protected $table = 't_BudgetTopDown';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'ScenarioID',
        'PeriodID',
        'BudgetLineID',
        'TotalTarget',
        'CreatedBy',
        'ModifiedBy',
    ];

    // Relationships
    public function scenario()
    {
        return $this->belongsTo(BudgetScenarioPlanning::class, 'ScenarioID');
    }

    public function period()
    {
        return $this->belongsTo(BudgetPeriods::class, 'PeriodID');
    }

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class, 'BudgetLineID');
    }

}
