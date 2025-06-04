<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class AddProperty extends Model
{
    //
    protected $table = 't_propertyregistry';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
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
        'GPSCoordinates',
        'PropertyDescription',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
        ];
    
}