<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceTrainingSession extends Model
{
    protected $table = 't_ComplianceTrainingSessions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Topic','TrainingTypeID','Facilitator','SessionDate','Duration',
        'MaterialsFileName','MaterialsMimeType','MaterialsFilePath',
        'CreatedBy','CreatedOn'
    ];
    
    public function participants()
    {
        return $this->hasMany(ComplianceTrainingParticipant::class,'TrainingID');
    }

    public function certifications()
    {
        return $this->hasMany(ComplianceCertification::class,'TrainingID');
    }
}
