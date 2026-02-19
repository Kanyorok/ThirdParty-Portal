<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobInterviewQuestionGroup extends Model
{
    protected $table = 't_HRJobInterviewQuestionGroups';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Description',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
    ];
}
