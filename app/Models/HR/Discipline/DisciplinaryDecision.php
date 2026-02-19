<?php

namespace App\Models\HR\Discipline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryDecision extends Model
{
    protected $table = 't_HRDisciplinaryDecisions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseID',
        'DecisionDate',
        'DecisionSummary',
        'PolicyClause',
        'LegalRefID',
        'InvestigationID',
        'HearingID',
        'SanctionID',
        'SanctionStartDate',
        'SanctionEndDate',
        'PayrollImpact',
        'Status',
        'ApprovedBy',
        'ApprovedOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'DecisionDate' => 'date',
        'SanctionStartDate' => 'date',
        'SanctionEndDate' => 'date',
        'PayrollImpact' => 'boolean',
        'ApprovedOn' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }

    public function legalRef(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryLegalRef::class, 'LegalRefID');
    }

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryInvestigation::class, 'InvestigationID');
    }

    public function hearing(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryHearing::class, 'HearingID');
    }

    public function sanction(): BelongsTo
    {
        return $this->belongsTo(DisciplinarySanction::class, 'SanctionID');
    }
}
