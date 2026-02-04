<?php

namespace App\Models\Inventory;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryType extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_InventoryTypes';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Type',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'CreatedOn',
        'ModifiedOn',
    ];

    protected $casts = [
        'Type' => 'string',
        'Status' => 'boolean',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
    ];

    public static function getPrimaryKey(): string
    {
        return 'InventoryTypesId';
    }

    public function items()
    {
        return $this->hasMany(ItemMasterList::class, 'InventoryType', 'Id');
    }

    public function type()
    {
        return $this->belongsTo(CodeDetail::class, 'Type', 'ID');
    }
}
