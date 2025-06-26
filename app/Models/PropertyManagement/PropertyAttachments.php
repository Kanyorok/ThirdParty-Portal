<?php

namespace App\Models\PropertyManagement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyAttachments extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_propertyattachments';
    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
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

    public static function getPrimaryKey(): string
    {
        return 'PropertyAttachmentId';
    }

    public function propertyid()
    {
        return $this->belongsTo(PropertyRegistry::class, 'PropertyID', 'Id');
    }
}
