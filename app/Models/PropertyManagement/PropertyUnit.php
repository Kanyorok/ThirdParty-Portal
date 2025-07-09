<?php

namespace App\Models\PropertyManagement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyUnit extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_PropertyUnit';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyID',
        'BlockID',
        'FloorID',
        'UnitCode',
        'UnitSize',
        'IsRentable',
        'CurrentStatus',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyUnitId';
    }
    // public function getBlockByProperty()
    // {
    //     return $this->hasMany(PropertyBlock::class, 'PropertyID', 'Id');
    // }
    //     public function getFloorByBlock()
    // {
    //     return $this->hasMany(PropertyFloor::class, 'BlockID', 'Id');
    // }
    public function unit()
    {
        return $this->belongsTo(PropertyBlock::class, 'BlockID', 'Id');
    }

    public function property()
    {
        return $this->belongsTo(PropertyRegistry::class, 'PropertyID', 'Id');
    }

    public function blocks()
    {
        return $this->belongsTo(PropertyBlock::class, 'BlockID', 'Id');
    }

    public function floors()
    {
        return $this->belongsTo(PropertyFloor::class, 'FloorID', 'Id');
    }
}
