<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetMonthlyProjectionAllocation extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';


    protected $table = 't_BudgetMonthlyProjectionsAllocation';

    public static function getPrimaryKey(): string
    {
        return 'BudgetMonthlyProjectionAllocationId';
    }

    protected $fillable = [
        'BudgetID',
        'BudgetProjectionID',
        'Month',
        'Allocation',
        'CreatedBy',
        'ModifiedBy',
    ];

    // Relationships
    public function budget()
    {
        return $this->belongsTo(Budget::class, 'BudgetID', 'Id');
    }

    public function budgetProjection()
    {
        return $this->belongsTo(BudgetDriverProjections::class, 'BudgetProjectionID', 'Id');
    }
}
