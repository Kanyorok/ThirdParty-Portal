<?php

namespace App\Models\HR\Discipline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryCaseStatusLog extends Model
{
    protected $table = 't_HRDisciplinaryCaseStatusLogs';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseID',
        'FromStatus',
        'ToStatus',
        'Remarks',
        'ChangedBy',
        'ChangedOn',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }
}
