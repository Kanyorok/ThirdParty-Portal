<?php

namespace App\Models\Inventory;

use App\Models\Procurement\Requisitions;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemType extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_ItemTypes';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'ItemTypesId';
    }

    protected $fillable = [
        'TypeName',
        'StockTracked',
        'RequiresTagging',
        'Active',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'TypeName'  => 'string',
        'StockTracked'  => 'boolean',
        'RequiresTagging'  => 'boolean',
        'Active'  => 'boolean',
        'CreatedBy'     => 'integer',
        'ModifiedBy'    => 'integer',
        'DeletedBy'     => 'integer',
        'CreatedOn'     => 'datetime',
        'ModifiedOn'    => 'datetime',
    ];

    public function requisitionitems()
    {
        return $this->hasMany(Requisitions::class, 'ItemTypeId', 'Id');
    }

}
