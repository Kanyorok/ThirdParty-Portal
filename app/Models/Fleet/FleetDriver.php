<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetDriver extends Model
{
    protected $table = 't_FleetDrivers';
    protected $primaryKey = 'DriverID';
    public $timestamps = false;

    protected $fillable = [
        'FullName', 'StaffNumber', 'NationalID', 'Phone', 'Email',
        'LicenseNumber', 'LicenseExpiryDate', 'LicenseCategory', 'EmploymentType',
        'Status', 'Notes', 'IsActive', 'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn',
    ];
}
