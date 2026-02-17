<?php

namespace App\Models\HR\Exit;

use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitDocument extends Model
{
    protected $table = 't_HRExitDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ExitID',
        'DocumentId',
        'DocType',
        'UploadedBy',
        'UploadedOn',
    ];

    protected $casts = [
        'UploadedOn' => 'datetime',
    ];

    public function exit(): BelongsTo
    {
        return $this->belongsTo(ExitRequest::class, 'ExitID');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }
}
