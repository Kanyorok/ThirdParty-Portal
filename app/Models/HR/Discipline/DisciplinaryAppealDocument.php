<?php

namespace App\Models\HR\Discipline;

use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryAppealDocument extends Model
{
    protected $table = 't_HRDisciplinaryAppealDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'AppealID',
        'DocumentId',
        'DocType',
        'Notes',
        'UploadedBy',
        'UploadedOn',
    ];

    public function appeal(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryAppeal::class, 'AppealID');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }
}
