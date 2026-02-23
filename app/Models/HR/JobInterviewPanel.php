<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobInterviewPanel extends Model
{
    protected $table = 't_HRJobInterviewPanels';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'InterviewID',
        'EmployeeID',
        'Role',
        'CreatedBy',
        'CreatedOn',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
