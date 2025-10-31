<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetRepairLog;
use App\Models\Fleet\FleetMaintenanceSchedule;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;
use App\Models\HRM\Employee;


class FleetTripLog extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_TripLogs';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'TripNo',
        'ParentTripID',
        'TripType',
        'TripCode',
        'VehicleType',
        'LoadType',
        'TripStartDate',
        'StartTime',
        'TripEndDate',
        'EndTime',
        'StartLocation',
        'EndLocation',
        'DistanceCovered',
        'Purpose',
        'Notes',
        'Status',
        'CreatedBy',
        'CreatedOn',

    ];

    public static function getPrimaryKey(): string
    {
        return 'TripId';
    }

    public function statusDetail()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }

   
    public function parentTripType()
    {
        return $this->belongsTo(CodeDetail::class, 'TripType', 'ID');
    }

    public function parentLoadType()
    {
        return $this->belongsTo(CodeDetail::class, 'LoadType', 'ID');
    }

    public function parentVehicleType()
    {
        return $this->belongsTo(CodeDetail::class, 'VehicleType', 'ID');
    }

    public function parentTrip()
    {
        return $this->belongsTo(FleetTripLog::class, 'ParentTripID', 'Id');
    }

    public function childTrips()
    {
        return $this->hasMany(FleetTripLog::class, 'ParentTripID', 'Id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

   public function employee()
        {
            return $this->belongsTo(Employee::class, 'EmployeeID', 'Id');
        }





}
