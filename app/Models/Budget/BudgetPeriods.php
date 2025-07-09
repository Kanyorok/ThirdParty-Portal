<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPeriods extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetPeriodsId';
    }

    protected $table = 't_BudgetPeriods';

    protected $fillable = [

        'fiscalYear',
        'periodType',
        'notes',
        'CreatedBy',
        'ModifiedBy',
    ];

    public function periodTypeID()
    {
        return $this->belongsTo(BudgetPeriodTypes::class, 'periodType');
    }
    public function periodType()
    {
        return $this->belongsTo(BudgetPeriodTypes::class, 'periodType', 'Id');
    }
}
