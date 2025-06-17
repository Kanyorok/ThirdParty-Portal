<?php

namespace App\Models\Budget;

use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetActivity extends Model
{
    use UserActorTrait,SoftDeletes;

    protected $table='t_BudgetActivities';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetActivityId';
    }

    protected $fillable = [
        'BudgetLineID',
        'BranchID',
        'ActivityName',
        'Description',
        'AllocationType',
        'FullAllocation',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $casts = [
        'CreatedOn'   => 'datetime',
        'ModifiedOn'  => 'datetime',
        'DeletedOn'   => 'datetime',
    ];
 
    public function allocations():HasMany
    {
        return $this->hasMany(BudgetMonthlyAllocation::class, 'BudgetActivityID', 'Id');
    }

    public function branch():BelongsTo
    {
        return $this->belongsTo(Branch::class,'BranchID','Id');
    }

    public function budgetLine():BelongsTo
    {
        return $this->belongsTo(BudgetLine::class,'BudgetLineID','Id');
    }
}
