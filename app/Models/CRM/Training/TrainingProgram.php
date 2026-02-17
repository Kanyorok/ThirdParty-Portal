<?php

namespace App\Models\CRM\Training;

use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
    protected $table = 't_CRMTrainingPrograms';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Title',
        'CategoryID',
        'DeliveryMode',
        'DurationHours',
        'Objectives',
        'TargetAudience',
        'BudgetedCost',
        'ActualCost',
        'IsMandatory',
        'HasCertification',
        'CertificateScope',
        'CertificationCompletionRule',
        'CertificationMinimumSessions',
        'Status',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'DurationHours' => 'decimal:2',
        'BudgetedCost' => 'decimal:2',
        'ActualCost' => 'decimal:2',
        'IsMandatory' => 'boolean',
        'HasCertification' => 'boolean',
        'CertificationMinimumSessions' => 'integer',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(TrainingCategory::class, 'CategoryID');
    }

    public function targets()
    {
        return $this->hasMany(TrainingProgramTarget::class, 'ProgramID');
    }

    public function sessions()
    {
        return $this->hasMany(TrainingSession::class, 'ProgramID');
    }
}
