<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyCategory extends Model
{
    //
    protected $table = 't_PropertyCategory';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyCategoryName', 
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
        ];

}