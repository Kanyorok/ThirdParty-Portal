<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Models\ThirdParty\ThirdParties;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicalFundContribution extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_MedicalFundContributions';
    protected $primaryKey = 'Id';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundId','ContributorType','ContributorId','Amount','ContributionDate','Notes',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'MedicalFundContributionId';
    }


    public function fund()
    {
        return $this->belongsTo(MedicalFund::class, 'FundId', 'Id');
    }
    public function contributorType()
    {
        return $this->belongsTo(CodeDetail::class, 'ContributorType', 'ID');
    }
    public function contributor()
    {
        return $this->belongsTo(ThirdParties::class, 'ContributorId', 'Id');
    }
}
