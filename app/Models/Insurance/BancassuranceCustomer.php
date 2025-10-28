<?php

namespace App\Models\Insurance;

use App\Models\Auth\User;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;

class BancassuranceCustomer extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_BancassuranceCustomers';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ThirdPartyId',
        'ReferralID',
        'DateOfBirth',
        'Gender',
        'MaritalStatus',
        'Occupation',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'BancassuranceCustomersId';
    }

    public function referrals()
    {
        return $this->belongsTo(BancAssuranceReferral::class, 'ReferralID', 'Id');
    }

    public function genders()
    {
        return $this->belongsTo(CodeDetail::class, 'Gender', 'ID');
    }

    public function maritalstatus()
    {
        return $this->belongsTo(CodeDetail::class, 'MaritalStatus', 'ID');
    }

    public function occupations()
    {
        return $this->belongsTo(CodeDetail::class, 'Occupation', 'ID');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function policies()
    {
        return $this->hasMany(BancassurancePolicy::class, 'CustomerID', 'Id');
    }

    public function thirdParty()
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }
}
