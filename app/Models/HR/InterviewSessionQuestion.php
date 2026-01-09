<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class InterviewSessionQuestion extends Model
{
    protected $table = 't_HRInterviewSessionQuestions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'QuestionID',
        'CreatedBy',
        'CreatedOn',
    ];

    public function question()
    {
        return $this->belongsTo(JobInterviewQuestion::class, 'QuestionID');
    }
}
