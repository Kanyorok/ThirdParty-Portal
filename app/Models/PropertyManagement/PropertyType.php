<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CategoryMaster;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyType extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_PropertyType';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyTypeName',
        'PropertyCategoryId',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
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
