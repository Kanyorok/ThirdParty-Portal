<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CategoryMaster;
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
   
    public function type()
    {
        return $this->belongsTo(PropertyType::class, 'PropertyType', 'Id');
    }
    public function propertyCategory()
    {
        return $this->belongsTo(CategoryMaster::class, 'Category', 'Id');
    }
    public function propertyLocality()
    {
        return $this->belongsTo(Locality::class, 'TownCity', 'Id');
    }
    public function attachment()
    {
        return $this->hasMany(PropertyAttachments::class,'PropertyID');
    }
}