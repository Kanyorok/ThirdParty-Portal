<?php

namespace App\Models\Inventory;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitOfMeasure extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_UOM';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'UOMId';
    }

    protected $fillable = [
        'Code',
        'Name',
        'BaseUnit',
        'Active',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'Code' => 'string',
        'Name' => 'string',
        'BaseUnit' => 'boolean',
        'Active' => 'boolean',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

}
