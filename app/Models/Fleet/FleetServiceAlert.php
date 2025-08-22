<?php


namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetMaintenanceSchedule;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;

class FleetServiceAlert extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
