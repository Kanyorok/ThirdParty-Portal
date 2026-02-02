<?php

namespace App\Models\Legal;

use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCaseEvidence extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalCaseEvidence';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'LegalCaseID',
        'EvidenceTitle',
        'Description',
        'DMSDocumentID',
        'ExternalLink',
        'IsActive',
        'UploadedBy',
        'UploadedOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalCaseEvidenceId';
    }

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'LegalCaseID', 'Id');
    }
}
