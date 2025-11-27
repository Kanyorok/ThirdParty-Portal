<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Core\Approval\CodeDetail;
use Illuminate\Support\Facades\Auth;
use App\Models\Auth\User;
use App\Models\HRM\Employee;
use App\Models\HRM\Department;
use App\Models\Fleet\FleetVehicle;
use App\Models\Fleet\FleetTripLog;

class FleetVehicleRequest extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetVehicleRequests';
    protected $primaryKey = 'Id';
    public $timestamps = false;


    protected $fillable = [
        'RequestID', 'RequestedBy', 'Department', 'RequestDate', 'TripNo', 'TripDate',
        'Purpose', 'FromLocation', 'ToLocation', 'PassengerCount',
        'PreferredVehicleType', 'Status', 'ApprovedBy',
        'ApprovedOn', 'RejectionReason', 'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn'];


    public static function getPrimaryKey(): string
    {
        return 'RequestId';
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'RequestedBy', 'Id');
    }

    public function trip()
    {
        return $this->belongsTo(FleetTripLog::class, 'TripNo', 'Id');
    }


    public function department()
    {
        return $this->belongsTo(Department::class, 'Department', 'Id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'ApprovedBy', 'Id');
    }

    public function statusDetail()
    {
        return $this->hasOne(CodeDetail::class, 'Value', 'Status')
            ->where('CodeID', 'VehicleRequestStatus')
            ->whereNull('DeletedOn');
    }

    public function vehicle()
    {
        return $this->belongsTo(CodeDetail::class, 'PreferredVehicleType', 'ID');
    }

    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }
}
