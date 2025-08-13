<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class ContractedDriver extends Model
{
    protected $table = 't_ContractedDrivers';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
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
        'IsActive',
    ];
}
