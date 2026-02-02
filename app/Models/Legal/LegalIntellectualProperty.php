<?php

namespace App\Models\Legal;

use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalIntellectualProperty extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalIntellectualProperties';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'Title',
        'IPType', // e.g. Trademark, Patent, Copyright
        'RegistrationNumber',
        'RegistrationDate',
        'ExpiryDate',
        'Status', // e.g. Active, Expired, Disputed, Pending Renewal
        'Owner',
        'DMSDocID', // DMS integration
        'Remarks',
        'IsDisputed',
        'DisputeReason',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalIntellectualPropertiesId';
    }

    protected $casts = [
        'IsDisputed' => 'boolean',
    ];
}
