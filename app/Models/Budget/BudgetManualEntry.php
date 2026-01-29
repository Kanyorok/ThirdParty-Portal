<?php

namespace App\Models\Budget;

use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetManualEntry extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_BudgetManualEntry';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'BudgetManualEntryId';
    }

    protected $fillable = [
        'BudgetID',
        'BranchID',
        'BudgetLineID',
        'Amount',
        'Comments',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];
    protected $casts = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'BudgetID', 'Id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id');
    }

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class, 'BudgetLineID', 'Id');
    }

    public function allocations()
    {
        return $this->hasMany(BudgetManualEntryAllocations::class, 'EntryID', 'Id');
    }
}
