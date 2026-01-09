<?php

namespace App\Models\HR\Discipline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplinaryAppeal extends Model
{
    protected $table = 't_HRDisciplinaryAppeals';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseID',
        'AppealDate',
        'Grounds',
        'DeadlineDate',
        'Status',
        'Outcome',
        'DecisionSummary',
        'HearingDate',
        'ApprovedBy',
        'ApprovedOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
    ];

    protected $casts = [
        'AppealDate' => 'date',
        'DeadlineDate' => 'date',
        'HearingDate' => 'datetime',
        'ApprovedOn' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }

    public function panel(): HasMany
    {
        return $this->hasMany(DisciplinaryAppealPanel::class, 'AppealID');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DisciplinaryAppealDocument::class, 'AppealID');
    }
}
