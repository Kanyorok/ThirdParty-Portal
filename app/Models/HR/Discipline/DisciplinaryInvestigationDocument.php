<?php

namespace App\Models\HR\Discipline;

use App\Models\DMS\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryInvestigationDocument extends Model
{
    protected $table = 't_HRDisciplinaryInvestigationDocuments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'InvestigationID',
        'DocumentId',
        'DocType',
        'Notes',
        'UploadedBy',
        'UploadedOn',
    ];

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryInvestigation::class, 'InvestigationID');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }
}
