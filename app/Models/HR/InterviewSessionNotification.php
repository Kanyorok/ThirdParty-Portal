<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class InterviewSessionNotification extends Model
{
    protected $table = 't_HRInterviewSessionNotifications';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'EmployeeID',
        'Message',
        'CreatedOn',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
