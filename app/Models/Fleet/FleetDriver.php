<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use App\Models\DMS\Image;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Models\HRM\Employee;
use App\Models\Fleet\FleetTripLog;


class FleetDriver extends Model
{
    use UserActorTrait, SoftDeletes,DocumentsTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetDrivers';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'DriverNo','FullName', 'StaffNumber', 'NationalID', 'Phone', 'Email', 'EmploymentType',
        'Status', 'Notes', 'DriverStatus', 'ImageId', 'IsActive', 'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DrvId';
    }

    public function assignments()
    {
        return $this->hasMany(FleetDriverAssignment::class, 'DriverID', 'Id');
    }

    public function vehicles()
    {
        return $this->hasManyThrough(
            FleetVehicle::class,
            FleetDriverAssignment::class,
            'DriverID',
            'Id',
            'Id',
            'VehicleID'
        );
    }

    public function trips()
    {
        return $this->hasManyThrough(
            FleetTripLog::class,
            FleetVehicleAssignment::class,
            'DriverID',
            'Id',
            'Id',
            'TripNo'
        )->whereNull('t_FleetVehicleAssignments.DeletedOn')
        ->whereNull('t_TripLogs.DeletedOn');
    }



    public function employmentType()
    {
        return $this->belongsTo(CodeDetail::class, 'EmploymentType', 'ID');
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'StaffNumber', 'Id');
    }

    public function tripLogs()
    {
        return $this->hasMany(FleetTripLog::class, 'DriverID', 'Id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'ImageId', 'ImageID');
    }

    public function driverImage()
    {
        return $this->belongsTo(Employee::class, 'ImageId', 'Id');
    }

    public function driverStatus()
    {
        return $this->belongsTo(CodeDetail::class, 'DriverStatus', 'ID');
    }


}
