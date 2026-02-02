<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_Budgets';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'Name',
        'FiscalYear',
        'From',
        'To',
        'Notes',
        'Status',

        'IsLimitSet',
        'IsReAllocated',
        'ApprovalOrRejectionReason',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $cast = [
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BudgetId';
    }

    public function activities()
    {
        return $this->hasMany(BudgetActivity::class, 'BudgetId', 'Id');
    }

    public function projections()
    {
        return $this->hasMany(BudgetDriverProjections::class, 'BudgetId', 'Id');
    }
}
