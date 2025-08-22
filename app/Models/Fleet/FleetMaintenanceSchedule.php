<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetServiceAlert;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;

class FleetMaintenanceSchedule extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetMaintenanceSchedules';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ScheduleID', 'VehicleID', 'MaintenanceType', 'ScheduledDate',
        'ScheduledMileage', 'Location', 'Notes', 'Status', 'MaintenanceStatus',
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
