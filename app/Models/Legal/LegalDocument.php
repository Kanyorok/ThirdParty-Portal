<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    protected $table = 't_LegalDocuments';
    protected $primaryKey = 'ID';
    public $timestamps = false;

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
        'ModifiedBy',
        'ModifiedOn',
        'IsActive',
    ];
}
