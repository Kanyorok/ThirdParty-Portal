<?php

namespace App\Models\Fleet;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetContractedDriverLicense extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

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
