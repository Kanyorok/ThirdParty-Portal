<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Models\ThirdParty\ThirdParties;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicalFundContributor extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_MedicalFundContributors';
    protected $primaryKey = 'Id';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundId','PartyId','ContributorNo','ThirdPartyId',
        'EffectiveFrom','EffectiveTo','Status',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    /**
     * Cast date attributes to Carbon instances so blade can call ->format() safely.
     */

    public static function getPrimaryKey(): string
    {
        return 'MedicalFundContributorsId';
    }

    public function fund()          
    { 
        return $this->belongsTo(MedicalFund::class, 'FundId','Id'); 
    }
    public function beneficiaries() 
    { 
        return $this->hasMany(MedicalFundBeneficiary::class, 'ContributorId','Id'); 
    }
    public function contributions() 
    { 
        return $this->hasMany(MedicalFundContribution::class, 'ContributorId','Id'); 
    }
    public function disbursements() 
    { 
        return $this->hasMany(MedicalFundDisbursement::class, 'ContributorId','Id'); 
    }
    public function thirdParty()  
    { 
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId','Id'); 
    }
    public function status()        
    { 
        return $this->belongsTo(CodeDetail::class, 'Status','ID'); 
    }
    public function type()        
    { 
        return $this->belongsTo(CodeDetail::class, 'ContributorType','ID'); 
    }
    public function getPackagePremiumTotalAttribute()
    {
        return (float) $this->packages()->sum('Premium');
    }

    public function packages() {
        return $this->belongsToMany(MedicalFundPackage::class, 't_MedicalFundContributorPackages', 'ContributorId', 'PackageId')
            ->withPivot([
                'IsActive',
                'SubscribedOn',
                'IsPrimary',
                'CreatedBy',
                'CreatedOn',
                'ModifiedBy',
                'ModifiedOn'
            ])->withTimestamps('CreatedOn', 'ModifiedOn');
    }
    public function primaryPackage() {
    return $this->packages()->wherePivot('IsActive',1)->wherePivot('IsPrimary',true)->first();
    }
}
