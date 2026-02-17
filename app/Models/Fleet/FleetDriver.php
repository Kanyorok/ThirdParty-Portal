<?php

namespace App\Models\Fleet;

use App\Models\Core\Approval\CodeDetail;
use App\Models\DMS\Image;
use App\Models\HR\Employee;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetDriver extends Model
{
    use UserActorTrait;
    use SoftDeletes;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
