<?php

namespace App\Models\PropertyManagement;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyLeaseRenewal extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    protected $table = 't_RenewLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
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
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LeaseRenewalId';
    }

    public function lease()
    {
        return $this->belongsTo(PropertyNewLease::class, 'LeaseNumber', 'Id');
    }

    public function paymentFrequency()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentFrequency', 'ID');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy');
    }
}
