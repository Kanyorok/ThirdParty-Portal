<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\ContractedDriver;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\HRM\Employee;


class FleetContractedDriverAssignment extends Model
{

      
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
