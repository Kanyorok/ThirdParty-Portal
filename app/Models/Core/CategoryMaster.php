<?php

namespace App\Models\Core;

use App\Models\PropertyManagement\PropertyType;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryMaster extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_CategoryMaster';
    protected $primaryKey = 'Id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'Name', 'Description', 'Type', 'Code',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];


    public static function getPrimaryKey(): string
    {
        return 'CategoryMasterId';
    }

    public function propertytypes(): HasMany
    {
        return $this->hasMany(PropertyType::class, 'PropertyCategoryId', 'Id');
    }

}
