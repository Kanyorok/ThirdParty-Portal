<?php

namespace App\Models\Fleet;

use App\Models\Core\Approval\CodeDetail;
use App\Models\ThirdParty\SupplierMaster;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetMaintenanceSchedule extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetMaintenanceSchedules';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ScheduleID', 'VehicleID', 'MaintenanceType', 'ScheduledDate',
        'ScheduledMileage', 'VendorID', 'Notes', 'Status', 'MaintenanceStatus',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'ScheduleId';
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function vendor()
    {
        return $this->belongsTo(SupplierMaster::class, 'VendorID', 'Id');
    }

    public function alert()
    {
        return $this->hasOne(FleetServiceAlert::class, 'ScheduleID', 'Id');
    }

    public function maintenanceType()
    {
        return $this->belongsTo(CodeDetail::class, 'MaintenanceType', 'ID');
    }

    public function maintenanceStatus()
    {
        return $this->belongsTo(CodeDetail::class, 'MaintenanceStatus', 'ID');
    }
}
