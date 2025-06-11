<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CategoryMaster;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyType extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_PropertyType';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyTypeName',
        'PropertyCategoryId',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyTypeId';
    }
    public function propertycategory()
    {
        return $this->belongsTo(CategoryMaster::class, 'PropertyCategoryId', 'Id');
    }
    public function property()
    {
        return $this->hasMany(PropertyRegistry::class, 'PropertyType', 'Id');
    }

}
