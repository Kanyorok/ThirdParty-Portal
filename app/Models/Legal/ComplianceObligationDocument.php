<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceObligationDocument extends Model
{
    protected $table = 't_ComplianceObligationDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ObligationID', 'FileName', 'MimeType', 'FilePath',
        'Version', 'UploadedBy', 'UploadedOn'
    ];
}
