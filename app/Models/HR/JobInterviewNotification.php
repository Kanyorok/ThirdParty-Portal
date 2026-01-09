<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobInterviewNotification extends Model
{
    protected $table = 't_HRInterviewNotifications';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'InterviewID',
        'EmployeeID',
        'Message',
        'CreatedOn',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
