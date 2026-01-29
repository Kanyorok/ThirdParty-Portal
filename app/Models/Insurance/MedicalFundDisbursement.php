<?php

namespace App\Models\Insurance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalFundDisbursement extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    protected $table = 't_MedicalFundDisbursements';
    protected $primaryKey = 'Id';

    public $timestamps = true;
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundId',
        'ContributorId',
        'BeneficiaryId',
        'CoverageID',
        'PackageID',
        'DisbursementDate',
        'Amount',
        'Purpose',
        'ApprovedBy',
        'ApprovedOn',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        // primary key column for this model is 'Id'
        return 'Id';
    }

    /**
     * Relationships
     */
    public function fund()
    {
        return $this->belongsTo(MedicalFund::class, 'FundId', 'Id');
    }

    public function contributor()
    {
        return $this->belongsTo(MedicalFundContributor::class, 'ContributorId', 'Id');
    }

    public function beneficiary()
    {
        return $this->belongsTo(MedicalFundBeneficiary::class, 'BeneficiaryId', 'Id');
    }

    public function coverage()
    {
        return $this->belongsTo(Coverage::class, 'CoverageID', 'Id');
    }

    public function package()
    {
        return $this->belongsTo(MedicalFundPackage::class, 'PackageID', 'Id');
    }
}
