<?php

namespace App\Models\Fleet;

use App\Models\Core\Approval\CodeDetail;
use App\Models\ThirdParty\SupplierMaster;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetRepairLog extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetRepairLogs';
    protected $primaryKey = 'Id';
    public $timestamps = false;


    protected $fillable = [
        'RepairID', 'VehicleID', 'ScheduleID', 'RepairType', 'RepairDate', 'VendorID', 'Cost',
        'Description', 'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'RepairId';
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function repairType()
    {
        return $this->belongsTo(CodeDetail::class, 'RepairType', 'ID');
    }

    public function vendor()
    {
        return $this->belongsTo(SupplierMaster::class, 'VendorID', 'Id');
    }

    public function schedule()
    {
        return $this->belongsTo(FleetMaintenanceSchedule::class, 'ScheduleID', 'Id');
    }
}
