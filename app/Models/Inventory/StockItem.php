<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;

class StockItem extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_StockItems';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'SKUCode',
        'ItemType',
        'Batch',
        'Serial',
        'Perishable',
        'Saleable',
        'Purchasable',
        'Store',
        'Branch',
        'CurrentQty',
        'Min',
        'Reorder',
        'Max',
        'LastReceived',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'SKUCode'   => 'string',
        'ItemType'   => 'string',
        'Batch'         => 'boolean',
        'Serial'        => 'boolean',
        'Perishable'    => 'boolean',
        'Saleable'      => 'boolean',
        'Purchasable'   => 'boolean',
        'Store'         => 'integer',
        'Branch'        => 'integer',
        'CurrentQty'   => 'integer',
        'Min'           => 'integer',
        'Reorder'       => 'integer',
        'Max'           => 'integer',
        'LastReceived' => 'datetime',
        'Status'        => 'boolean',
        'CreatedBy'     => 'integer',
        'ModifiedBy'    => 'integer',
        'DeletedBy'     => 'integer',
        'CreatedOn'     => 'datetime',
        'ModifiedOn'    => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }
    public function store()
   {
    return $this->belongsTo(\App\Models\Inventory\Store::class, 'Store', 'Id');
   }
   public function branch()
{
    return $this->belongsTo(\App\Models\Core\Branch::class, 'Branch', 'Id');
}



}