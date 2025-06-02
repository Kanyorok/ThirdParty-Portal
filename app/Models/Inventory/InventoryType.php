<?php

namespace App\Models\Inventory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;


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
        'Type'  => 'string',
        'Status'  => 'boolean',
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



}