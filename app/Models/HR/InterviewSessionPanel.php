<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class InterviewSessionPanel extends Model
{
    protected $table = 't_HRInterviewSessionPanels';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
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
