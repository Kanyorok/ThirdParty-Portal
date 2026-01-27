<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceControlEvidence extends Model
{
    protected $table = 't_ComplianceControlEvidence';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ControlID', 'FileName', 'MimeType', 'FilePath',
        'Version', 'UploadedBy', 'UploadedOn',
    ];

    public function control()
    {
        return $this->belongsTo(ComplianceControl::class, 'ControlID');
    }
}
