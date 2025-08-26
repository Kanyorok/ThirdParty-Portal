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

class FleetDriver extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetDrivers';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'DriverNo','FullName', 'StaffNumber', 'NationalID', 'Phone', 'Email', 'EmploymentType',
        'Status', 'Notes', 'IsActive',     'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DrvId';
    }

    public function employmentType()
    {
        return $this->belongsTo(CodeDetail::class, 'EmploymentType', 'ID');
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'StaffNumber', 'Id');
    }


}
