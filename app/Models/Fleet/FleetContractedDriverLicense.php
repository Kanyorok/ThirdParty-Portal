<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetContractedDriverLicense extends Model
{
    protected $table = 't_ContractedDriverLicenses';
    protected $primaryKey = 'LicenseID';
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
    ];

    public function driver()
    {
        return $this->belongsTo(FleetContractedDriver::class, 'ContractedDriverID');
    }
}
