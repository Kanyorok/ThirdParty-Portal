<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobInterviewQuestion extends Model
{
    protected $table = 't_HRJobInterviewQuestions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'GroupID',
        'Title',
        'Guidance',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
    ];

    public function group()
    {
        return $this->belongsTo(JobInterviewQuestionGroup::class, 'GroupID');
    }
}
