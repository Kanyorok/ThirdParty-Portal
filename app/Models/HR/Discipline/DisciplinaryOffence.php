<?php

namespace App\Models\HR\Discipline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryOffence extends Model
{
    protected $table = 't_HRDisciplinaryOffences';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CategoryID',
        'Code',
        'Name',
        'Severity',
        'RecommendedSanctionID',
        'HearingRequired',
        'SummaryDismissalAllowed',
        'RequiresEvidence',
        'RequiresApproval',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'HearingRequired' => 'boolean',
        'SummaryDismissalAllowed' => 'boolean',
        'RequiresEvidence' => 'boolean',
        'RequiresApproval' => 'boolean',
        'IsActive' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryOffenceCategory::class, 'CategoryID');
    }

    public function recommendedSanction(): BelongsTo
    {
        return $this->belongsTo(DisciplinarySanction::class, 'RecommendedSanctionID');
    }
}
