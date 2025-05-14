<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class VendorClarification extends Model
{
    use SoftDeletes; 

    protected $table = 't_VendorClarifications';
    protected $primaryKey = 'ClarificationID';
    public $incrementing = true; 

    protected $fillable = [
        'TenderID',
        'Id',
        'Question',
        'QuestionDate',
        'Answer',
        'AnswerDate',
        'IsPublishedToAll',
    ];

    protected $casts = [
        'QuestionDate' => 'datetime',
        'AnswerDate' => 'datetime',
    ];
}
