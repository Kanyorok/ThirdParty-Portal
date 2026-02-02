<?php

namespace App\Models\Fleet;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetServiceAlert extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetServiceAlerts';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'AlertID', 'ScheduleID', 'RepairID', 'VehicleID', 'AlertType', 'Description',
        'TriggerMileage', 'TriggerDate',
        'IsAcknowledged', 'AcknowledgedOn', 'AcknowledgedBy', 'MaintenanceStatus',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'AlertId';
    }

    public function schedule()
    {
        return $this->belongsTo(FleetMaintenanceSchedule::class, 'ScheduleID', 'Id');
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }
}
