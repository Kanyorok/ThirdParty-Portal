<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Model;
use App\Models\Fleet\FleetVehicle;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;
use App\Models\Insurance\InsuranceProvider;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;
use App\Models\Auth\User;


class FleetInsuranceTracker extends Model
{

    use UserActorTrait, SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

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
        'DocumentPath',
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
