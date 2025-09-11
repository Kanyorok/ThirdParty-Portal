<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Auth\User;
class PropertyLeaseSchedule extends Model
{
    use SoftDeletes, UserActorTrait;
    protected $table = 't_ScheduleLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeaseNumber',
        'PaymentFrequency',
        'StartDate',
        'EndDate',
        'BaseRent',
        'ServiceCharge',
        'ParkingFee',
        'OtherCharges',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'ScheduleLeaseId';
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
