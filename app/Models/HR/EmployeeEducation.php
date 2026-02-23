<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeeEducation extends Model
{
    protected $table = 't_HREmployeeEducation';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'Level',
        'Institution',
        'Course',
        'YearFrom',
        'YearTo',
        'Grade',
        'CreatedBy',
        'CreatedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];
}
