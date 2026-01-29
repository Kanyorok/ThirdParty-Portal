<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetPeriods extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
