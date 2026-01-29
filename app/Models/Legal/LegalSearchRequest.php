<?php

namespace App\Models\Legal;

use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalSearchRequest extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalSearchRequests';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'RequestType',
        'EntityName',
        // 'EntityType',
        // 'RegistrationNumber',
        // 'Country',
        'RequestedBy',
        'RequestDate',
        'Status',
        // 'IsActive',
        'Remarks',
        'Findings',
        'ApprovalReason',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalSearchRequestsId';
    }
}
