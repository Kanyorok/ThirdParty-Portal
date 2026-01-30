<?php

namespace App\Models\Insurance;

use App\Enums\Insurance\InsurancePolicyStatus;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassurancePolicy extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_BancassurancePolicies';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'CustomerID', 'ProductID', 'InsurerID', 'PolicyNumber', 'SumAssured',
        'PremiumAmount', 'PolicyStartDate', 'PolicyEndDate', 'PaymentFrequency',
        'ReferralID', 'IssuedDate', 'ExpiryDate', 'IsActive', 'Status', 'RiderAddOnId', 'CreatedBy',
        'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
    'Status' => InsurancePolicyStatus::class,
    'PolicyStartDate' => 'date',
    'PolicyEndDate' => 'date',
    'IssuedDate' => 'date',
    'ExpiryDate' => 'date',
    ];

    /**
     * Get the renewals for the policy.
     */
    public function renewals()
    {
        return $this->hasMany(PolicyRenewal::class, 'PolicyID', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'bancassurancepolicyId';
    }

    public function customer()
    {
        return $this->belongsTo(BancassuranceCustomer::class, 'CustomerID', 'Id');
    }

    public function product()
    {
        return $this->belongsTo(InsuranceProduct::class, 'ProductID', 'Id');
    }

    public function insurer()
    {
        return $this->belongsTo(InsuranceProvider::class, 'InsurerID', 'Id');
    }

    public function paymentfrequency()
    {
        return $this->belongsTo(CodeDetail::class, 'PaymentFrequency', 'ID');
    }

    public function referral()
    {
        return $this->belongsTo(BancAssuranceReferral::class, 'ReferralID', 'Id');
    }

    public function rideraddon()
    {
        return $this->belongsTo(InsuranceProductRider::class, 'RiderAddOnId', 'Id');
    }
}
