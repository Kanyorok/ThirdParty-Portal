<?php

namespace App\Models\Procurement\Prequalification;

use App\Models\Procurement\Criteria;
use App\Models\Procurement\Section;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrequalificationCriteria extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_PrequalificationRoundCriteria';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RoundId',
        'SectionId',
        'CriteriaId',
        'Included',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(PrequalificationRound::class, 'RoundId', 'RoundID');
    }

    public function masterSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'SectionId', 'Id');
    }

    public function masterCriteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'CriteriaId', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }
}
