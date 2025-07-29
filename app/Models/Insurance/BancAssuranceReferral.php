<?php

namespace App\Models\Insurance;

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
        'RefferedBy', 'ReferralDate', 'InsuranceProdeuctId', 'PreferredInsurerId',
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
}
