<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;

class PriceManagement extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_Pricing';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';


    protected $fillable = [
        'PriceID',
        'ItemID',
        'UOM',
        'EstimatedPrice',
        'ActualPrice',
        'CurrencyCode',
        'EffectiveFrom',
        'EffectiveTo',
        'IsDefault',
        'Source',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'PriceManagementID';
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOM', 'Id');
    }

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

}
