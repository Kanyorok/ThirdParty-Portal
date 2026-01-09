<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobInterviewQuestionAssignment extends Model
{
    protected $table = 't_HRJobInterviewQuestionAssignments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'InterviewID',
        'QuestionID',
        'PanelistID',
        'AssignedBy',
        'AssignedOn',
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
