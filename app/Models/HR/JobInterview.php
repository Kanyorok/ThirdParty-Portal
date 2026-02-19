<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobInterview extends Model
{
    protected $table = 't_HRJobInterviews';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ApplicationID',
        'JobOpeningID',
        'InterviewType',
        'InterviewDate',
        'Panel',
        'Location',
        'Status',
        'Score',
        'Feedback',
        'Recommendation',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'ApplicationID');
    }

    public function opening()
    {
        return $this->belongsTo(JobOpening::class, 'JobOpeningID');
    }

    public function panelMembers()
    {
        return $this->hasMany(JobInterviewPanel::class, 'InterviewID');
    }

    public function questionAssignments()
    {
        return $this->hasMany(JobInterviewQuestionAssignment::class, 'InterviewID');
    }

    public function scores()
    {
        return $this->hasMany(JobInterviewScore::class, 'InterviewID');
    }

    public function notifications()
    {
        return $this->hasMany(JobInterviewNotification::class, 'InterviewID');
    }
}
