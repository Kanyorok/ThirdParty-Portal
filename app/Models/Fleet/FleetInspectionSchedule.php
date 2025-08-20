<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;
use App\Models\HRM\Employee;

class FleetInspectionSchedule extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetInspectionSchedule';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'InspectionNo',
        'VehicleID',
        'InspectionType',
        'InspectionDate',
        'DueDate',
        'Status',
        'Inspector',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    // Relationships

    public static function getPrimaryKey(): string
    {
        return 'InspectionId';
    }
    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function inspectionStatus()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }

    public function inspector()
    {
        return $this->belongsTo(Employee::class, 'Inspector', 'Id');
    }


}
