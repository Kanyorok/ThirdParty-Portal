<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\Locality;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyRegistry extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_PropertyRegistry';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyName',
        'PropertyCode',
        'PropertyType',
        'Category',
        'Owner',
        'AcquisitionDate',
        'Country',
        'TownCity',
        'AreaLocality',
        'PropertyDescription',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
        ];
    
    public static function getPrimaryKey(): string
    {
        return 'PropertyRegistryId';
    }
    public function propertycategory()
    {
        return $this->belongsTo(PropertyCategory::class, 'Category', 'Id');
    }
    public function propertylocality()
    {
        return $this->belongsTo(Locality::class, 'TownCity', 'ID');
    }
    public function attchment()
    {
        return $this->hasMany(PropertyAttachments::class,'PropertyID');
    }
    public function Block()
    {
        return $this->hasMany(PropertyBlock::class,'PropertyID');
    }
}