<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
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
        'ReferralID', 'IssuedDate', 'ExpiryDate', 'IsActive', 'CreatedBy',
        'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'bancassurancepolicyId';
    }

    public function customer()
    {
        return $this->belongsTo(BancassuranceCustomers::class, 'CustomerID', 'Id');
    }

    Public function product()
    {
        return $this->belongsTo(CodeDetail::class, 'InsuranceProductId', 'ID');
    }

    public function insurer()
    {
        return $this->belongsTo(CodeDetail::class, 'PreferredInsurerId', 'ID');
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