<?php

namespace App\Models\FleetManagement;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
use App\Models\Core\CodeDetail;


class DriverManagement extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';


    protected $table = 't_Drivers';
    protected $connection = 'sqlsrv';
    protected $primaryKey = 'Id';
    protected $fillable = [

        'DriverID',
        'DriverName',
        'LicenseNumber',
        'LicenseExpiryDate',
        'EmploymentStatus',
        'Phone',
        'Email',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'DriverId';
    }

    public function status()
    {
        return $this->belongsTo(CodeDetail::class, 'EmploymentStatus', 'ID');
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    //
}
