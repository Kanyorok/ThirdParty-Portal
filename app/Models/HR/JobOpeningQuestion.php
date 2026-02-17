<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobOpeningQuestion extends Model
{
    protected $table = 't_HRJobOpeningQuestions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'JobOpeningID',
        'QuestionID',
        'CreatedBy',
        'CreatedOn',
    ];
}
