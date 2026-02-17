<?php

namespace App\Models\HR\Discipline;

use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryCaseDocument extends Model
{
    protected $table = 't_HRDisciplinaryCaseDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'CaseID',
        'DocumentId',
        'DocType',
        'Title',
        'Notes',
        'UploadedBy',
        'UploadedOn',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryCase::class, 'CaseID');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }
}
