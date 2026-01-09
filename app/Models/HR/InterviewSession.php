<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class InterviewSession extends Model
{
    protected $table = 't_HRInterviewSessions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'JobOpeningID',
        'RoundNo',
        'RoundLabel',
        'InterviewType',
        'InterviewDate',
        'Location',
        'Status',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'InterviewDate' => 'date',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
    ];

    public function opening()
    {
        return $this->belongsTo(JobOpening::class, 'JobOpeningID');
    }

    public function candidates()
    {
        return $this->hasMany(InterviewSessionCandidate::class, 'SessionID');
    }

    public function panelMembers()
    {
        return $this->hasMany(InterviewSessionPanel::class, 'SessionID');
    }

    public function sessionQuestions()
    {
        return $this->hasMany(InterviewSessionQuestion::class, 'SessionID');
    }

    public function questionAssignments()
    {
        return $this->hasMany(InterviewSessionQuestionAssignment::class, 'SessionID');
    }

    public function notifications()
    {
        return $this->hasMany(InterviewSessionNotification::class, 'SessionID');
    }

    public function questions()
    {
        return $this->belongsToMany(
            JobInterviewQuestion::class,
            't_HRInterviewSessionQuestions',
            'SessionID',
            'QuestionID'
        );
    }
}
