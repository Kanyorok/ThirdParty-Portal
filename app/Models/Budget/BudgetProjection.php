<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetProjection extends Model
{
    use UserActorTrait,softDeletes;

    protected $table = 't_BudgetProjection';
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
}
