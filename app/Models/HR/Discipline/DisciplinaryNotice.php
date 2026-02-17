<?php

namespace App\Models\HR\Discipline;

use App\Models\DMS\Document;
use App\Models\Legal\LegalTemplate;
use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryNotice extends Model
{
    protected $table = 't_HRDisciplinaryNotices';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseID',
        'NoticeType',
        'TemplateID',
        'NoticeDocumentId',
        'IssuedOn',
        'ResponseDueOn',
        'Status',
        'Summary',
        'DeliveryMethod',
        'DeliveryStatus',
        'SentBy',
        'SentOn',
        'AcknowledgedBy',
        'AcknowledgedOn',
        'CreatedBy',
        'CreatedOn',
    ];

    protected $casts = [
        'IssuedOn' => 'date',
        'ResponseDueOn' => 'date',
        'AcknowledgedOn' => 'datetime',
        'SentOn' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LegalTemplate::class, 'TemplateID');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'NoticeDocumentId');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'AcknowledgedBy');
    }
}
