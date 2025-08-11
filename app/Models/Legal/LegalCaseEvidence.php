<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalCaseEvidence extends Model
{
    protected $table = 't_LegalCaseEvidence';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'LegalCaseID', 'EvidenceTitle', 'Description', 'DMSDocumentID',
        'ExternalLink', 'UploadedBy', 'UploadedOn', 'IsActive'
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'LegalCaseID');
    }
}
