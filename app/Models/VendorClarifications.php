<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorClarifications extends Model
{
    // Define the table name (Laravel assumes table names are lowercase and plural by default)
    protected $table = 't_VendorClarifications';
    protected $primaryKey = 'ClarificationID';
    public $incrementing = true;

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'ClarificationID',
        'TenderID',
        'VendorID',
        'Question',
        'QuestionDate',
        'Answer',
        'AnswerDate',
        'ISPUBLISHEDTOALL'
    ];

    // Define the data types for specific columns (optional, if you need to cast them)
    protected $casts = [
        'QuestionDate' => 'datetime',
        'AnswerDate' => 'datetime',
        'ISPUBLISHEDTOALL' => 'boolean',
    ];

    // Disable timestamps if your table doesn't have created_at and updated_at columns
    public $timestamps = false;
}