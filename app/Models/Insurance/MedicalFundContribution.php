<?php

namespace App\Models\Insurance;

use App\Models\Core\Approval\CodeDetail;
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
        // align with other models which use FundId -> Id
        return $this->belongsTo(MedicalFund::class, 'FundId', 'Id');
    }

    public function contributor()
    { 
        // contributions table uses ContributorId (matching other models)
        return $this->belongsTo(MedicalFundContributor::class, 'ContributorId','Id'); 
    }

    /**
     * Optional relation to code detail describing the contributor/type for this contribution.
     * This allows displaying a human-friendly description for the ContributorType field.
     */
    public function type()
    {
        return $this->belongsTo(CodeDetail::class, 'ContributorType', 'ID');
    }
}
