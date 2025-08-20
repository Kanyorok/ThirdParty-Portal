<?php

namespace App\Models\Budget;

use App\Models\BR\Product;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProjection extends Model
{
    use UserActorTrait, softDeletes;

    protected $table = 't_BudgetProjections';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    public static function getPrimaryKey(): string
    {
        return 'BudgetProjectionId';
    }

    protected $fillable = [
        'BudgetID',
        'BudgetLineID',
        'ProductID',
        'NumberOfAccounts',
        'AllocationType',
        'FullAllocation',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];
    protected $cast = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'BudgetID', 'Id');
    }

    public function budgetLine()
    {
        return $this->belongsTo(BudgetLine::class, 'BudgetLineID', 'Id');
    }

    public function product()
    {
        return $this->belongsTo(BudgetProduct::class, 'ProductID', 'Id');
    }

    public function allocations()
    {
        return $this->hasMany(BudgetProjectionData::class, 'BudgetProjectionID', 'Id');
    }

    public function getTotalAllocationAttribute()
    {
        return $this->allocations()->sum('Amount');
    }
}
