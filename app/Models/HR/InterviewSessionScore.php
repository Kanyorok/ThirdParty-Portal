<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class InterviewSessionScore extends Model
{
    protected $table = 't_HRInterviewSessionScores';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionCandidateID',
        'QuestionID',
        'PanelistID',
        'Score',
        'Comment',
        'CreatedBy',
        'CreatedOn',
    ];

    public function question()
    {
        return $this->belongsTo(JobInterviewQuestion::class, 'QuestionID');
    }

    public function panelist()
    {
        return $this->belongsTo(Employee::class, 'PanelistID');
    }

    public function candidate()
    {
        return $this->belongsTo(InterviewSessionCandidate::class, 'SessionCandidateID');
    }
}
