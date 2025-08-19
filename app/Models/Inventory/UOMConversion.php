<?php

namespace App\Models\Inventory;
use App\Traits\Model\UserActorTrait;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Auth\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UOMConversion extends Model
{
     use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    /**
     * The table associated with the model.
     *
     * @var string
     */

    protected $table = 't_UOMConversions';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'UOMNo',
        'Item',
        'UOM',
        'AlternateUOM',
        'ConversionFactor',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn'
    ];

    public static function getPrimaryKey(): string
    {
        return 'uomConversionId';
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'Item', 'Id');
    }

    public function uom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'UOM', 'Id');

    }

    public function alternateUom()
    {
        return $this->belongsTo(UnitOfMeasure::class, 'AlternateUOM', 'Id');
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

