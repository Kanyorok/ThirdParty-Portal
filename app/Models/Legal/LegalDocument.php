<?php

namespace App\Models\Legal;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalDocument extends Model
{
    use SoftDeletes, UserActorTrait;

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
        'ModifiedBy',
        'IsActive',
    ];

    public static function getPrimaryKey() : string 
    {
        return 'FinanceCDNotesId'; 
    }

}
