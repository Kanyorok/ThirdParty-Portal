<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobInterviewScore extends Model
{
    protected $table = 't_HRJobInterviewScores';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'InterviewID',
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
}
