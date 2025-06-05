<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    //
    protected $table = 't_PropertyType';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
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

    public function propertycategory()
    {
        return $this->belongsTo(PropertyCategory::class, 'PropertyCategoryId', 'Id');
    }

}
