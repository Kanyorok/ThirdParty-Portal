<?php

namespace App\Models\PropertyManagement;

use App\Enums\Property\PropertyNewLeaseEnum;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyNewLease extends Model
{
    use SoftDeletes, UserActorTrait;
    protected $table = 't_LeaseCreation';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Tenant',
        'LeaseNumber',
        'PropertyID',
        'BlockID',
        'FloorID',
        'Unit',
        'StartDate',
        'EndDate',
        'PaymentFrequency',
        'MonthlyRent',
        'Deposit',
        'ServiceCharge',
        'ParkingFee',
        'OtherCharges',
        'DueDay',
        'SpecialTerms',
        'Status',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'LeaseId';
    }
    protected $casts = [
        'Status' => PropertyNewLeaseEnum::class,
    ];
    public function tenant()
    {
        return $this->belongsTo(PropertyNewTenant::class, 'Tenant', 'Id');
    }
    public function property()
    {
        return $this->belongsTo(PropertyRegistry::class, 'PropertyID', 'Id');
    }
    public function block()
    {
        return $this->belongsTo(PropertyBlock::class, 'BlockID', 'Id');
    }
    public function floor()
    {
        return $this->belongsTo(PropertyFloor::class, 'FloorID', 'Id');
    }
    public function unit()
    {
        return $this->belongsTo(PropertyUnit::class, 'Unit', 'Id');
    }
    public function code()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentFrequency', 'ID');
    }

}
