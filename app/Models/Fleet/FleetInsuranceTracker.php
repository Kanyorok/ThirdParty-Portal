<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetInsuranceTracker extends Model
{
    protected $table = 't_FleetInsuranceTracker';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID',
        'InsuranceProvider',
        'PolicyNumber',
        'CoverageStartDate',
        'CoverageEndDate',
        'PremiumAmount',
        'RenewalReminderDate',
        'Notes',
        'DocumentPath',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }
}
