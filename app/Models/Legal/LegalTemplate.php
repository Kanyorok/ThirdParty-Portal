<?php

namespace App\Models\Legal;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalTemplate extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalTemplates';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'Title',
        'DocumentType',
        'Description',
        'TemplateBody',
        'DocumentDMSID',
        'Version',
        'Status',
        'ApprovalReason',
        'ApprovalStatus',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalTemplatesId';
    }

    protected $casts = [
        'IsActive' => 'boolean',
    ];

}
