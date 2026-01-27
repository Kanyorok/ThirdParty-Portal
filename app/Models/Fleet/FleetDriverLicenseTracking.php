<?php

namespace App\Models\Fleet;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetDriverLicenseTracking extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetDriverLicenseTracking';
    protected $primaryKey = 'Id';
    public $timestamps = false;


    protected $fillable = [
        'DriverID',
        'LicenseNumber',
        'LicenseCategory',
        'IssueDate',
        'ExpiryDate',
        'RenewalDate',
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
        return $this->belongsTo(FleetDriver::class, 'DriverID', 'Id');
    }
}
