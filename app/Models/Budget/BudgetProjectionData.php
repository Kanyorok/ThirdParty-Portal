<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProjectionData extends Model
{
    use UserActorTrait,SoftDeletes;

    protected $table = 't_BudgetProjectionsData';
    protected $primaryKey = 'Id';

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    public static function getPrimaryKey(): string
    {
        return 'BudgetProjectionId';
    }

    protected $fillable = [
        'BudgetProjectionID',
        'ProductID',
        'BudgetID',
        'ProductID',
        'Amount',
        'Month',

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
}
