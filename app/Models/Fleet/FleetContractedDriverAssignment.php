<?php

namespace App\Models\Fleet;

use App\Models\HRM\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetContractedDriverAssignment extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetContractedDriverAssignments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'DriverID',
        'VehicleID',
        'AssignmentDate',
        'UnassignmentDate',
        'Purpose',
        'Notes',
        'AssignedBy',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'AssgId';
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function driver()
    {
        return $this->belongsTo(ContractedDriver::class, 'DriverID', 'Id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(Employee::class, 'AssignedBy', 'Id');
    }
}
