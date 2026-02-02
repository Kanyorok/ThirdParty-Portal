<?php

namespace App\Models\Fleet;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\InsuranceProvider;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FleetInsuranceTracker extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_FleetInsuranceTracker';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'InsuranceNo',
        'VehicleID',
        'InsuranceProvider',
        'PolicyNumber',
        'CoverageStartDate',
        'CoverageEndDate',
        'PremiumAmount',
        'RenewalReminderDate',
        'Notes',
        'Status',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'InsuranceId';
    }

    public function vehicle()
    {
        return $this->belongsTo(FleetVehicle::class, 'VehicleID', 'Id');
    }

    public function insurance()
    {
        return $this->belongsTo(InsuranceProvider::class, 'InsuranceProvider', 'Id');
    }

    public function insuranceStatus()
    {
        return $this->belongsTo(CodeDetail::class, 'Status', 'ID');
    }
}
