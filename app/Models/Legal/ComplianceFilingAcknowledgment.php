<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceFilingAcknowledgment extends Model
{
    protected $table = 't_ComplianceFilingAcknowledgments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'FilingID', 'AckFileName', 'MimeType', 'FilePath',
        'UploadedBy', 'UploadedOn'
    ];

    public function filing()
    {
        return $this->belongsTo(ComplianceFiling::class, 'FilingID');
    }
}
