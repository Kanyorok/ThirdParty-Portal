<?php

namespace App\Models\Budget;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetDriver extends Model
{
    use UserActorTrait, SoftDeletes;

    protected $table='t_BudgetDrivers';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable=[
        'DriverName',
        'IsActive',

        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
    ];

    protected $cast=[
        'IsActive'=>'boolean',
        'CreatedOn'   => 'datetime',
        'ModifiedOn'  => 'datetime',
        'DeletedOn'   => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'BudgetDriverId';
    }
}
