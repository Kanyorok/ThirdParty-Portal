<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
class PropertyLeaseSchedule extends Model
{
    use SoftDeletes, UserActorTrait;
    protected $table = 't_ScheduleLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeaseNumber',
        'TenantId',
        'PropertyId',
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
    public function tenant()
    {
        return $this->belongsTo(PropertyNewTenant::class, 'TenantId', 'Id');
    }
    public function property()
    {
        return $this->belongsTo(PropertyRegistry::class, 'PropertyId', 'Id');
    }
    public function paymentFrequency()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentFrequency', 'ID');
    }
}