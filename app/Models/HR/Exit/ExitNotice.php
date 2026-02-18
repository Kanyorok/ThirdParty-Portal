<?php

namespace App\Models\HR\Exit;

use App\Models\DMS\Document;
use App\Models\Legal\LegalTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitNotice extends Model
{
    protected $table = 't_HRExitNotices';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ExitID',
        'NoticeType',
        'TemplateID',
        'DeliveryMethod',
        'DocumentId',
        'IssuedOn',
        'ResponseDue',
        'Status',
        'Summary',
        'DeliveryStatus',
        'SentBy',
        'SentOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IssuedOn' => 'date',
        'ResponseDue' => 'date',
        'SentOn' => 'datetime',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(ExitRequest::class, 'ExitID');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LegalTemplate::class, 'TemplateID');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }
}
