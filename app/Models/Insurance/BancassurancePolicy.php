<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use App\Enums\Insurance\InsurancePolicyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassurancePolicy extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_BancassurancePolicies';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'CustomerID', 'ProductID', 'InsurerID', 'PolicyNumber', 'SumAssured',
        'PremiumAmount', 'PolicyStartDate', 'PolicyEndDate', 'PaymentFrequency',
        'ReferralID', 'IssuedDate', 'ExpiryDate', 'IsActive','Status', 'CreatedBy',
        'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'bancassurancepolicyId';
    }

    protected $casts = [
        'Status' => InsurancePolicyStatus::class,
    ];
    public function customer()
    {
        return $this->belongsTo(BancassuranceCustomers::class, 'CustomerID', 'Id');
    }

    Public function product()
    {
        return $this->belongsTo(CodeDetail::class, 'ProductID', 'ID');
    }

    public function insurer()
    {
        return $this->belongsTo(CodeDetail::class, 'InsurerID', 'ID');
    }

    public function paymentfrequency()
    {
        return $this->belongsTo(CodeDetail::class,'PaymentFrequency', 'ID');
    }

    public function referral()
    {
        return $this->belongsTo(BancAssuranceReferral::class, 'ReferralID', 'Id');
    }
}