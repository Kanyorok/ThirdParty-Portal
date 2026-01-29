<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetTopDown extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';

    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_BudgetTopDown';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'ScenarioID',
        'PeriodID',
        'BudgetLineID',
        'TotalTarget',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BudgetTopDownId';
    }

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
