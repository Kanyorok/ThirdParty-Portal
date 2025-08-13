<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetVehicleRequest extends Model
{
    protected $table = 't_FleetVehicleRequests';
    public $timestamps = false;

    protected $fillable = [
        'RequestedBy', 'Department', 'RequestDate', 'TripDate',
        'Purpose', 'FromLocation', 'ToLocation', 'PassengerCount',
        'PreferredVehicleType', 'Status', 'ApprovedBy',
        'ApprovedOn', 'RejectionReason', 'CreatedOn', 'ModifiedOn'
    ];

    public function requester()
    {
        return $this->belongsTo(\App\Models\User::class, 'RequestedBy');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'ApprovedBy');
    }
}
