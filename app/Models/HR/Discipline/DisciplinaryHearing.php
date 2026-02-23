<?php

namespace App\Models\HR\Discipline;

use App\Models\DMS\Document;
use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplinaryHearing extends Model
{
    protected $table = 't_HRDisciplinaryHearings';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseID',
        'HearingDate',
        'Venue',
        'HRFacilitatorID',
        'EmployeeRepName',
        'Status',
        'MinutesDocumentId',
        'PanelRecommendation',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'HearingDate' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }

    public function facilitator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'HRFacilitatorID');
    }

    public function minutes(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'MinutesDocumentId');
    }

    public function panelMembers(): HasMany
    {
        return $this->hasMany(DisciplinaryHearingPanel::class, 'HearingID');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(DisciplinaryHearingAttendance::class, 'HearingID');
    }
}
