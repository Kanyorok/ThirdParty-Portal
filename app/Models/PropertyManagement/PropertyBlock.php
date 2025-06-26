<?php

namespace App\Models\PropertyManagement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyBlock extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_PropertyBlock';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyID',
        'BlockName',                                                                                                 
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
       
        ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyBlockId';
    }
    public function property()
    {
        return $this->belongsTo(PropertyRegistry::class,'PropertyID','Id');
    }
        public function getFloorByBlock()
    {
        return $this->hasMany(PropertyFloor::class, 'BlockID', 'Id');
    }
    public function floor()
    {
        return $this->hasMany(PropertyFloor::class,'BlockID');
    }
}

