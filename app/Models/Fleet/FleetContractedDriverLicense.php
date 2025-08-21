<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\ContractedDriver;
use App\Traits\Model\UserActorTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class FleetContractedDriverLicense extends Model
{
    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ContractedDriverLicenses';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ContractedDriverID',
        'LicenseNumber',
        'LicenseCategory',
        'IssueDate',
        'ExpiryDate',
        'Notes',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LicenseId';
    }


    public function driver()
    {
        return $this->belongsTo(ContractedDriver::class, 'ContractedDriverID', 'Id');
    }


}
