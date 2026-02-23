<?php

namespace App\Models\HR\Discipline;

use App\Models\DMS\Document;
use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplinaryInvestigation extends Model
{
    protected $table = 't_HRDisciplinaryInvestigations';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseID',
        'InvestigatorType',
        'InvestigatorID',
        'InvestigatorName',
        'StartDate',
        'EndDate',
        'ConflictDeclared',
        'FindingsSummary',
        'Status',
        'ApprovedBy',
        'ApprovedOn',
        'ReportDocumentId',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'StartDate' => 'date',
        'EndDate' => 'date',
        'ConflictDeclared' => 'boolean',
        'ApprovedOn' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }

    public function investigator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'InvestigatorID');
    }

    public function reportDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'ReportDocumentId');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DisciplinaryInvestigationDocument::class, 'InvestigationID');
    }
}
