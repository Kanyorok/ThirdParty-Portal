<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractedDriver extends Model
{

    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ContractedDrivers';
    protected $primaryKey = 'Id';
    public $timestamps = false;



    protected $fillable = [
        'DriverNo',
        'FullName',
        'NationalID',
        'Phone',
        'CompanyName',
        'ContractStartDate',
        'ContractEndDate',
        'LicenseNumber',
        'LicenseExpiryDate',
        'LicenseCategory',
        'Status',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
        'IsActive',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DriverId';
    }
}
