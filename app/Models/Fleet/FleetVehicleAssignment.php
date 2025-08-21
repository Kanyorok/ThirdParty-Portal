<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetVehicleAssignment extends Model
{
    protected $table = 't_FleetVehicleAssignments';
    protected $primaryKey = 'AssignmentID';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID',
        'AssignedBranchID',
        'AssignedToUserID',
        'AssignmentDate',
        'Purpose',
        'Notes',
        'AssignedBy',
        'CreatedOn'
    ];

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class, 'AssignedBranchID');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'AssignedToUserID');
    }

    public function assignedBy()
{
    return $this->belongsTo(\App\Models\User::class, 'AssignedBy');
}
}
