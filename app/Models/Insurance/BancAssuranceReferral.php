<?php

namespace App\Models\Insurance;

use App\Models\Auth\User;
use App\Models\HRM\Employee;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\Insurance\InsuranceReferralStatus;

class BancAssuranceReferral extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceReferrals';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ClientName', 'ClientIDNumber', 'ClientPhone', 'ClientEmail', 'BranchId',
        'ReferredBy', 'ReferralDate', 'InsuranceProductId', 'PreferredInsurerId',
        'Remarks', 'Status', 'AssignedTo', 'ConvertedPolicyId', 'CreatedBy',
        'ModifiedBy', 'DeletedBy'
    ];

    protected $casts = [
        'Status' => InsuranceReferralStatus::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'bancassuarancereferralId';
    }

    public function insuranceProduct()
    {
        return $this->belongsTo(InsuranceProduct::class, 'InsuranceProductId', 'Id');
    }

    public function preferredInsurer()
    {
        return $this->belongsTo(InsuranceProvider::class, 'PreferredInsurerId', 'Id');
    }

    public function assignedToUser()
    {
        return $this->belongsTo(User::class, 'AssignedTo', 'Id');
    }

    public function referredByEmployee()
    {
        return $this->belongsTo(employee::class, 'ReferredBy', 'Id');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }
    public function employee()
    {
        return $this->belongsTo(user::class, 'ReferredBy', 'Id');
    }
}
