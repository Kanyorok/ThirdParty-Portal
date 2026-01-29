<?php

namespace App\Models\Fleet;

use App\Models\Core\Approval\CodeDetail;
use App\Models\HRM\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetInspectionSchedule extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
