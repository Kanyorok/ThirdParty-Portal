<?php

namespace App\Models\Legal;

use App\Models\Auth\User;
use App\Models\DMS\Document;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalDocument extends Model
{
    use SoftDeletes, UserActorTrait,DocumentsTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false; // We are using CreatedOn & ModifiedOn instead of created_at & updated_at

    protected $fillable = [
        'DocumentTitle',
        'DocumentType',
        'SourceModule',
        'SourceID',
        'LinkedDMSDocID',
        'ReviewStatus',
        'ExecutionStatus',
        'DispatchDate',
        'SignOffDate',
        'ReviewedBy',
        'ReviewedOn',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedOn',
        'ModifiedBy',
        'IsActive',
    ];

    public static function getPrimaryKey() : string
    {
        return 'LegalDocumentId';
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable')->latest();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }
}
