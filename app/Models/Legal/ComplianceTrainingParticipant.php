<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceTrainingParticipant extends Model
{
    protected $table = 't_ComplianceTrainingParticipants';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['TrainingID','UserID','Attended','RegisteredOn'];

    public function training()
    {
        return $this->belongsTo(ComplianceTrainingSession::class,'TrainingID');
    }

    
}