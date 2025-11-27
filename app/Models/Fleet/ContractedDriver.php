<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Fleet\FleetTripLog;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetVehicleAssignment;
use App\Models\Fleet\FleetContractedDriverLicense;
use App\Models\Fleet\FleetContractedDriverAssignment;
use App\Models\Core\Approval\CodeDetail;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\DocumentsTrait;
use App\Models\ThirdParty\ThirdParties;


class ContractedDriver extends Model
{

    use UserActorTrait, SoftDeletes, DocumentsTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        return $this->belongsTo(ThirdParties::class, 'CompanyID', 'Id');
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
