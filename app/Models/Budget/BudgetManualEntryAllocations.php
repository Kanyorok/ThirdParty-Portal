<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetManualEntryAllocations extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_BudgetManualEntryAllocations';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'BudgetManualEntryAllocationsId';
    }

    protected $fillable = [
        'EntryID',
        'BudgetID',
        'Month',
        'Allocation',
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

    public function entry()
    {
        return $this->hasMany(BudgetManualEntry::class, 'EntryID', 'Id');
    }
}
