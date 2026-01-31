<?php

namespace App\Models\Legal;

use App\Models\Auth\User;
use App\Models\Core\Module;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalDocument extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
        'DueDate',
        'ExpiryDate',
        'CreatedBy',
        'CreatedOn',
        'ModifiedOn',
        'ModifiedBy',
        'IsActive',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalDocumentId';
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modules()
    {
        return $this->belongsTo(Module::class, 'ModuleID', 'SourceID');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }
}
