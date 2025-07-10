<?php

namespace App\Models\PropertyManagement;

use App\Models\Core\CodeDetail;
use Illuminate\Database\Eloquent\Model;

class PropertyLeaseRenewal extends Model
{
    //
    protected $table = 't_RenewLease';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'LeaseNumber',
        'TenantId',
        'PropertyId',
        'PaymentFrequency',
        'EndDateCurrentLease',
        'NewStartDate',
        'NewEndDate',
        'NewMonthlyRent',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'ScheduleRenewalId';
    }

    public function getPropertyByTenant()
    {
        return $this->hasMany(PropertyNewLease::class, 'TenantId', 'Id');
    }

    public function getLeaseByProperty()
    {
        return $this->hasMany(PropertyNewLease::class, 'PropertyId', 'Id');
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
