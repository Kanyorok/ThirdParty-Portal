<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceFiling extends Model
{
    protected $table = 't_ComplianceFilings';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'TemplateID','SubmissionDate','FileName','MimeType','FilePath',
        'Status','Notes','SubmittedBy','CreatedOn'
    ];

    public function template()
    {
        return $this->belongsTo(ComplianceFilingTemplate::class,'TemplateID');
    }

    public function acknowledgments()
    {
        return $this->hasMany(ComplianceFilingAcknowledgment::class,'FilingID');
    }
}
