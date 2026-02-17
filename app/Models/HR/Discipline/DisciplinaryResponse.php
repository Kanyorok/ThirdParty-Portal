<?php

namespace App\Models\HR\Discipline;

use App\Models\DMS\Document;
use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryResponse extends Model
{
    protected $table = 't_HRDisciplinaryResponses';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseID',
        'NoticeID',
        'ResponseText',
        'SubmittedBy',
        'SubmittedOn',
        'Status',
        'ResponseDocumentId',
    ];

    protected $casts = [
        'SubmittedOn' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }

    public function notice(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryNotice::class, 'NoticeID');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'SubmittedBy');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'ResponseDocumentId');
    }
}
