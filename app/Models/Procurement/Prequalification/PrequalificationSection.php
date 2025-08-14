<?php

namespace App\Models\Procurement\Prequalification;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Procurement\Section;
use App\Models\Procurement\Criteria;

class PrequalificationSection extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_PrequalificationRoundSections';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'RoundId',
        'SectionId',
        'Weight',
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
        return $this->belongsTo(Section::class, 'SectionId', 'id');
    }

    public function criteria()
    {
        return $this->hasMany(PrequalificationCriteria::class, 'SectionId', 'SectionId')
            ->with('masterCriteria');
    }

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }
}
