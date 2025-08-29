<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
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
        'VehicleID',
        'DriverType',
        'DriverID',
        'TripStartDate',
        'StartTime',
        'TripEndDate',
        'EndTime',
        'StartLocation',
        'EndLocation',
        'DistanceCovered',
        'Purpose',
        'Notes',
        'CreatedBy',
        'CreatedOn',

    ];

    public static function getPrimaryKey(): string
    {
        return 'TripId';
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function driverType()
    {
        return $this->belongsTo(CodeDetail::class, 'DriverType', 'ID');
    }


    public function driverPermanent()
    {
        return $this->belongsTo(FleetDriver::class, 'DriverID', 'Id');
    }

    public function driverContracted()
    {
        return $this->belongsTo(ContractedDriver::class, 'DriverID', 'Id');
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
