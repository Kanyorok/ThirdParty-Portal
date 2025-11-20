<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalFund extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_MedicalFunds';
    protected $primaryKey = 'Id';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundName','ProviderId','CoverageType','CoverageLimit','Description','IsActive',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'MedicalFundId';
    }

    // Relationships
    public function provider()
    {
        // Adjust model/keys if your provider model differs
        return $this->belongsTo(InsuranceProvider::class, 'ProviderId', 'Id');
    }

    public function beneficiaries()
    {
        return $this->hasMany(MedicalFundBeneficiary::class, 'FundId', 'Id');
    }

    public function contributions()
    {
        return $this->hasMany(MedicalFundContribution::class, 'FundId', 'Id');
    }

    public function disbursements()
    {
        return $this->hasMany(MedicalFundDisbursement::class, 'FundId', 'Id');
    }

    public function contributors() 
    { 
        return $this->hasMany(MedicalFundContributor::class, 'FundId', 'Id'); 
    }

    public function packages() 
    { 
        return $this->hasMany(MedicalFundPackage::class,'FundId','Id');
    }

    public function coverages()
    {
        return $this->belongsTo(CodeDetail::class, 'CoverageType', 'ID');
    }
    
}
