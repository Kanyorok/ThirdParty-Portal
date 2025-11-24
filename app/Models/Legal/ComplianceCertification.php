<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceCertification extends Model
{
    protected $table = 't_ComplianceCertifications';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'UserID','TrainingID','CertificationName','IssueDate','ExpiryDate',
        'Status','CreatedBy','CreatedOn'
    ];

    public function training()
    {
        return $this->belongsTo(ComplianceTrainingSession::class,'TrainingID');
    }
}