<?php

namespace App\Models\CRM\Training;

use App\Models\BR\Client;
use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Model;

class TrainingCertificate extends Model
{
    protected $table = 't_CRMTrainingCertificates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'ProgramID',
        'CertificateScope',
        'TemplateID',
        'ClientID',
        'CertificationName',
        'IssuingBody',
        'CertificateNumber',
        'IssuedOn',
        'ExpiresOn',
        'DocumentId',
        'Status',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IssuedOn' => 'date',
        'ExpiresOn' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'SessionID');
    }

    public function program()
    {
        return $this->belongsTo(TrainingProgram::class, 'ProgramID');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'ClientID', 'ClientID');
    }

    public function template()
    {
        return $this->belongsTo(TrainingCertificateTemplate::class, 'TemplateID');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }
}
