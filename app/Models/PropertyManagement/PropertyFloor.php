<?php

namespace App\Models\PropertyManagement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyFloor extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_PropertyFloor';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyID',
        'BlockID',
        'FloorLabel',
        'FloorNotes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyFloorId';
    }

    public function units()
    {
        return $this->hasMany(PropertyUnit::class, 'FloorID');
    }

    public function block()
    {
        return $this->belongsTo(PropertyBlock::class, 'BlockID', 'Id');
    }
}
