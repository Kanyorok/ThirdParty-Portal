<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;

class FleetAlertRule extends Model
{
    protected $table = 't_FleetAlertRules';
    public $timestamps = false;

    protected $fillable = [
        'Name', 'AlertType', 'Description', 'TriggerMileage',
        'TriggerDays', 'Frequency', 'EscalationLevel',
        'IsActive', 'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn'
    ];
}
