<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyAttachments extends Model
{
    //
    protected $table = 't_propertyattachments';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyID',
        'DocumentTitle',
        'DocumentType',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
        ];
}
