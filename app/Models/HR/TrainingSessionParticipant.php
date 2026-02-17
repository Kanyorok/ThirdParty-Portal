<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class TrainingSessionParticipant extends Model
{
    protected $table = 't_HRTrainingSessionParticipants';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'EmployeeID',
        'EnrollmentMethod',
        'Status',
        'AttendanceStatus',
        'AttendanceMarkedOn',
        'AttendanceMarkedBy',
        'EnrolledOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'AttendanceMarkedOn' => 'datetime',
        'EnrolledOn'         => 'datetime',
        'CreatedOn'          => 'datetime',
        'ModifiedOn'         => 'datetime',
        'DeletedOn'          => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'SessionID');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
