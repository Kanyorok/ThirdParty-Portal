<?php

namespace App\Models\CRM\Training;

use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Model;

class TrainingCertificateTemplate extends Model
{
    protected $table = 't_CRMTrainingCertificateTemplates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ProgramID',
        'Name',
        'Description',
        'TemplateBody',
        'BackgroundImagePath',
        'DefaultIssuingBody',
        'DefaultValidityMonths',
        'TemplateDocumentId',
        'IsSample',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsSample' => 'boolean',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(TrainingProgram::class, 'ProgramID');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'TemplateDocumentId');
    }

    public function certificates()
    {
        return $this->hasMany(TrainingCertificate::class, 'TemplateID');
    }
}
