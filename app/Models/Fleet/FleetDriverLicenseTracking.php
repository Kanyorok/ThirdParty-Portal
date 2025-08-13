<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetDriverLicenseTracking extends Model
{
    protected $table = 't_FleetDriverLicenseTracking';
    public $timestamps = false;

    protected $fillable = [
        'DriverID',
        'LicenseNumber',
        'LicenseCategory',
        'IssuedDate',
        'ExpiryDate',
        'RenewalDate',
        'Notes',
        'CreatedOn',
        'CreatedBy'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'DriverID');
    }
}
