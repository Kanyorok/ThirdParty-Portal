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
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_BudgetActivities';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'BudgetID',
        'BudgetLineID',
        'BranchID',
        'ActivityID',
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
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BudgetActivityId';
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(BudgetMonthlyAllocation::class, 'BudgetActivityID', 'Id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    }

    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class, 'BudgetLineID', 'Id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(BudgetActivityMaster::class, 'ActivityID', 'Id');
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'BudgetID', 'Id');
    }
}
