<?php

namespace App\Models\HR;

use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Model;

class TrainingCertificate extends Model
{
    protected $table = 't_HRTrainingCertificates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'EmployeeID',
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
        'IssuedOn'   => 'date',
        'ExpiresOn'  => 'date',
        'CreatedOn'  => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn'  => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'SessionID');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }
}
