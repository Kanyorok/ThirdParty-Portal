<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use App\Models\PropertyManagement\PropertyNewLease;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyLeaseRenewal extends Model
{

     use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_RenewLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeaseNumber',
        'PaymentFrequency',
        'EndDateCurrentLease',
        'NewStartDate',
        'NewEndDate',
        'NewMonthlyRent',
        'ServiceCharge',
        'ParkingFee',
        'OtherCharges',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
     public static function getPrimaryKey(): string
    {
        return 'ScheduleRenewalId';
    }
    public function lease()
    {
        return $this->belongsTo(PropertyNewLease::class, 'LeaseNumber', 'Id');
    }
     public function paymentFrequency()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentFrequency', 'ID');
    }
}
