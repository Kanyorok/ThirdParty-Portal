<?php

namespace App\Models\Legal;

use App\Models\DMS\Document;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCaseEvidence extends Model
{
    use SoftDeletes, UserActorTrait, DocumentsTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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

    /**
     * Relation to uploaded documents.
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }
}
