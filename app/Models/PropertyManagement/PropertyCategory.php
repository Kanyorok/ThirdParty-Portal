<?php

namespace App\Models\PropertyManagement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyCategory extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_PropertyCategory';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyCategoryName', 
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
        ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyCategoryId';
    }
    public function type(): HasMany
    {
        return $this->hasMany(PropertyType::class, 'PropertyCategoryId');
    }
    public function property(): HasMany
    {
        return $this->hasMany(PropertyRegistry::class, 'Category', 'Id');
    }
}