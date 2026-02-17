<?php

namespace App\Models\HR\Exit;

use App\Models\HR\Employee;
use App\Models\HR\Discipline\DisciplinaryCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExitRequest extends Model
{
    protected $table = 't_HRExitRequests';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ExitNo',
        'EmployeeID',
        'ExitTypeID',
        'PolicyID',
        'CaseID',
        'RedundancyID',
        'InitiatorType',
        'InitiatedBy',
        'InitiatedOn',
        'RequestedOn',
        'Reason',
        'NoticeDate',
        'ProposedLastDay',
        'EffectiveExitDate',
        'NoticeDays',
        'NoticePayInLieu',
        'NoticePayAmount',
        'NoticeWaived',
        'ApprovalStatus',
        'ApprovedBy',
        'ApprovedOn',
        'FinalPayrollRunID',
        'FinalPayrollStatus',
        'FinalPayrollOn',
        'Status',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'InitiatedOn' => 'datetime',
        'RequestedOn' => 'date',
        'NoticeDate' => 'date',
        'ProposedLastDay' => 'date',
        'EffectiveExitDate' => 'date',
        'NoticePayInLieu' => 'boolean',
        'NoticeWaived' => 'boolean',
        'ApprovedOn' => 'datetime',
        'FinalPayrollOn' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }

    public function exitType(): BelongsTo
    {
        return $this->belongsTo(ExitType::class, 'ExitTypeID');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(ExitPolicy::class, 'PolicyID');
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }

    public function redundancy(): BelongsTo
    {
        return $this->belongsTo(ExitRedundancy::class, 'RedundancyID');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ExitStatusLog::class, 'ExitID')->orderByDesc('Id');
    }

    public function notices(): HasMany
    {
        return $this->hasMany(ExitNotice::class, 'ExitID');
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(ExitClearance::class, 'ExitID');
    }

    public function terminalDues(): HasMany
    {
        return $this->hasMany(ExitTerminalDue::class, 'ExitID');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ExitDocument::class, 'ExitID');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(ExitInterview::class, 'ExitID');
    }
}
