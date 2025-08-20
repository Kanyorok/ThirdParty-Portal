<?php

namespace App\Models\Legal;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalSearchRequest extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
