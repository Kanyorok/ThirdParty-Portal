<?php

namespace App\Models\Insurance;

use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancassuranceClaimAssessment extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use DocumentsTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $table = 't_BancassuranceClaimAssessments';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ClaimId', 'AssessedBy', 'AssessmentDate', 'AssessmentAmount', 'AssessmentComments',
        'Decision', 'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'bancassuranceclaimassessmentId';
    }

    public function claim()
    {
        return $this->belongsTo(BancassuranceClaim::class, 'ClaimId', 'Id');
    }

    public function assessedby()
    {
        return $this->belongsTo(User::class, 'AssessedBy', 'Id');
    }

    public function decision()
    {
        return $this->belongsTo(CodeDetail::class, 'Decision', 'ID');
    }

    public function claimpaiyments()
    {
        return $this->hasMany(BancassuranceClaimPayment::class, 'ClaimId', 'ClaimId');
    }
}
