<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalExecutionLog extends Model
{
    protected $table = 't_LegalExecutionLogs';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'LegalDocumentID',
        'SignedBy',
        'SignedOn',
        'LinkedDMSDocID',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'IsActive'
    ];

    public function document()
    {
        return $this->belongsTo(LegalDocument::class, 'LegalDocumentID');
    }
}
