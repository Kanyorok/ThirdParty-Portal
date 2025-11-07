<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CategoryMaster;
use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
class PropertyRegistry extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait;
    //
    protected $table = 't_PropertyRegistry';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyName',
        'PropertyCode',
        'PropertyType',
        'Category',
        'Owner',
        'AcquisitionDate',
        'Address',
        'CountryId',
        'LocationId',
        'PropertyDescription',
        'IsActive',
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

    public function getBlockByProperty()
    {
        return $this->hasMany(PropertyBlock::class, 'PropertyID', 'Id');
    }
    public function propertyCategory()
    {
        return $this->belongsTo(CategoryMaster::class, 'Category', 'Id');
    }
    public function propertyLocality()
    {
        return $this->belongsTo(Locality::class, 'LocationId', 'Id');
    }

    public function propertyCountry()
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
    }

    public function getFloorByBlock()
    {
        return $this->hasMany(PropertyFloor::class, 'BlockID', 'Id');
    }
    public function attachment()
    {
        return $this->hasMany(PropertyAttachments::class, 'PropertyID', 'Id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }
}
