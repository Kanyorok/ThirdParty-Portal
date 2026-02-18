<?php

namespace App\Models\HR\Discipline;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplinaryCase extends Model
{
    protected $table = 't_HRDisciplinaryCases';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseNo',
        'EmployeeID',
        'ComplainantType',
        'ComplainantID',
        'ComplainantName',
        'OffenceID',
        'PolicyID',
        'Severity',
        'IncidentDate',
        'ReportedDate',
        'Description',
        'Status',
        'HearingRequired',
        'SummaryDismissalAllowed',
        'EvidenceRequired',
        'ClosedOn',
        'ClosedBy',
        'Outcome',
        'RetentionUntil',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IncidentDate' => 'date',
        'ReportedDate' => 'date',
        'ClosedOn' => 'datetime',
        'RetentionUntil' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }

    public function complainant(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'ComplainantID');
    }

    public function offence(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryOffence::class, 'OffenceID');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryPolicy::class, 'PolicyID');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DisciplinaryCaseDocument::class, 'CaseID');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(DisciplinaryCaseStatusLog::class, 'CaseID')->orderBy('ChangedOn');
    }

    public function investigations(): HasMany
    {
        return $this->hasMany(DisciplinaryInvestigation::class, 'CaseID');
    }

    public function hearings(): HasMany
    {
        return $this->hasMany(DisciplinaryHearing::class, 'CaseID');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(DisciplinaryDecision::class, 'CaseID');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(DisciplinaryAppeal::class, 'CaseID');
    }
}
