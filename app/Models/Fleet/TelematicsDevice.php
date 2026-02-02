<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelematicsDevice extends Model
{
    use SoftDeletes;

    protected $table = 't_TelematicsDevices';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'VehicleID',
        'DeviceID',
        'Provider',
        'InstallDate',
        'SubscriptionStatus',
        'RenewalDate',
        'TrackingURL',
        'ApiKey',
        'Notes',
        'CreatedBy',
        'ModifiedBy',
    ];

    // 🛠️ Tell Laravel to use your custom soft delete column
    public const DELETED_AT = 'DeletedOn';

    protected $dates = ['InstallDate', 'RenewalDate', 'DeletedOn'];

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID');
    }
}
