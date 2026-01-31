<?php

namespace App\Models\Legal;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCaseOutcome extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalCaseOutcomes';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'LegalCaseID', 'Outcome', 'JudgmentDate', 'JudgeName',
        'CourtDecision', 'PenaltyAmount', 'Remarks',
        'CreatedBy', 'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalCaseOutcomesId';
    }

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'LegalCaseID', 'ID');
    }
}
