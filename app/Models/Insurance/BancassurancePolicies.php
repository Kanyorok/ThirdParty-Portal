<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

class BancassurancePolicies extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_BancassurancePolicies';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'CustomerID',
        'ProductID',
        'ProviderID',
        'PolicyNumber',
        'SumAssured',
        'PremiumAmount',
        'InsurerID',
        'PolicyStartDate',
        'PolicyEndDate',
        'PaymentFrequency',
        'ReferralID',
        'StartDate',
        'EndDate',
        'Status',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
        public static function getPrimaryKey(): string
    {
        return 'BancassurancePoliciesId';
    }
}
