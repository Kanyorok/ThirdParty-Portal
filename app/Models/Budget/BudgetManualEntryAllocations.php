<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetManualEntryAllocations extends Model
{
    use UserActorTrait,SoftDeletes;
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'DeletedOn'
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

}
