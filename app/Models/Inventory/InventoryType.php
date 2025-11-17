<?php

namespace App\Models\Inventory;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Core\CodeDetail;


class InventoryType extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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

    public function type()
    {
        return $this->belongsTo(CodeDetail::class, 'Type', 'ID');
    }
}
