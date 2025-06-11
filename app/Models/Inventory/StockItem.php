<?php

namespace App\Models\Inventory;

use App\Models\Core\Branch;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockItem extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_StockItems';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'StockItemsId';
    }

    protected $fillable = [
        'SKUCode',
        'ItemID',
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
        'ItemID'   => 'string',
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


    public function store()
   {
       return $this->belongsTo(Store::class, 'Store', 'Id');
   }
   public function branch()
{
    return $this->belongsTo(Branch::class, 'Branch', 'Id');
}
public function item()
{
    return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
}



}
