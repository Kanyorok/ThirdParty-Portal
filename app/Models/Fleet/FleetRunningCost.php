<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetRunningCost extends Model
{
    protected $table = 't_FleetRunningCosts';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID', 'CostType', 'CostDate', 'Amount',
        'Vendor', 'Notes', 'CreatedBy', 'CreatedOn',
    ];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }
}
