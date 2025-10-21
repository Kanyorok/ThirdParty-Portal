<?php

namespace App\Models\Insurance;

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
        return $this->belongsTo(MedicalFund::class, 'FundID', 'ID');
    }

    public function contributor()
    { 
        return $this->belongsTo(MedicalFundContributor::class, 'ContributorID','ID'); 
    }
}
