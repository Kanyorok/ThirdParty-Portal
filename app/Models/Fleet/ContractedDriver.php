<?php

namespace App\Models\Fleet;

use App\Models\ThirdParty\SupplierMaster;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractedDriver extends Model
{
    use UserActorTrait;
    use SoftDeletes;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_ContractedDrivers';
    protected $primaryKey = 'Id';
    public $timestamps = false;


    protected $fillable = [
        'DriverNo',
        'FullName',
        'NationalID',
        'Phone',
        'CompanyID',
        'ContractStartDate',
        'ContractEndDate',
        'IsActive',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DriverId';
    }

    public function company()
    {
        return $this->belongsTo(SupplierMaster::class, 'CompanyID', 'Id');
    }

    public function tripLogs()
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

    public function licenses()
    {
        return $this->hasMany(FleetContractedDriverLicense::class, 'ContractedDriverID', 'Id');
    }

    public function assignments()
    {
        return $this->hasMany(FleetContractedDriverAssignment::class, 'DriverID', 'Id');
    }

    public function vehicles()
    {
        return $this->hasManyThrough(
            FleetVehicle::class,
            FleetContractedDriverAssignment::class,
            'DriverID',
            'Id',
            'Id',
            'VehicleID'
        );
    }
}
