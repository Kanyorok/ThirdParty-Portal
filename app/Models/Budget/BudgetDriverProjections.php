<?php

namespace App\Models\Budget;

use App\Models\Core\Currency;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetDriverProjections extends Model
{
    use UserActorTrait,SoftDeletes;

    protected $table='t_BudgetDriverProjections';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetDriverProjectionsId';
    }

    protected $fillable =[
        'ScenarioID',
        'CurrencyID',
        'PeriodID',
        'CreatedBy',
        'ModifiedBy',
    ];

    // Relationships


}
