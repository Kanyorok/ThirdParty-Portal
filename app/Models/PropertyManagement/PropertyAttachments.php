<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyAttachments extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;


    protected $table = 't_propertyattachments';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'PropertyID',
        'DocumentTitle',
        'DocumentType',
        'Description',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'PropertyAttachmentId';
    }

    public function property()
    {
        return $this->belongsTo(PropertyRegistry::class, 'PropertyID', 'Id');
    }

    public function documenttype()
    {
        return $this->belongsTo(CodeDetail::class, 'DocumentType', 'ID');
    }
}
