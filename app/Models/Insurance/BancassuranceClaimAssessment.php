<?php

namespace App\Models\Insurance;

use App\Models\Core\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceClaimAssessment extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceClaimAssessments';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ClaimId','AssessedBy','AssessmentDate','AssessmentAmount','AssessmentComments',
        'Decision','CreatedBy','ModifiedBy','DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'bancassuranceclaimassessmentId';
    }

}
