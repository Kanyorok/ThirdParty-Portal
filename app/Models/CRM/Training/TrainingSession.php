<?php

namespace App\Models\CRM\Training;

use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    protected $table = 't_CRMTrainingSessions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ProgramID',
        'SessionCode',
        'Title',
        'StartDate',
        'EndDate',
        'StartTime',
        'EndTime',
        'Location',
        'OnlineLink',
        'TrainerID',
        'MaxParticipants',
        'Status',
        'AgendaDocumentId',
        'MaterialsDocumentId',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'StartDate' => 'date',
        'EndDate' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(TrainingProgram::class, 'ProgramID');
    }

    public function trainer()
    {
        return $this->belongsTo(TrainingTrainer::class, 'TrainerID');
    }

    public function agendaDocument()
    {
        return $this->belongsTo(Document::class, 'AgendaDocumentId');
    }

    public function materialsDocument()
    {
        return $this->belongsTo(Document::class, 'MaterialsDocumentId');
    }

    public function participants()
    {
        return $this->hasMany(TrainingSessionParticipant::class, 'SessionID');
    }

    public function feedback()
    {
        return $this->hasMany(TrainingSessionFeedback::class, 'SessionID');
    }
}
