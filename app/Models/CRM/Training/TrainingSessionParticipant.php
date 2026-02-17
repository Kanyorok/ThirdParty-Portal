<?php

namespace App\Models\CRM\Training;

use App\Models\BR\Client;
use Illuminate\Database\Eloquent\Model;

class TrainingSessionParticipant extends Model
{
    protected $table = 't_CRMTrainingSessionParticipants';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'SessionID',
        'ClientID',
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
        'EnrolledOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'SessionID');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'ClientID', 'ClientID');
    }
}
