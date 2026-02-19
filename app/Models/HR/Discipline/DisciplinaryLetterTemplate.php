<?php

namespace App\Models\HR\Discipline;

use App\Models\Legal\LegalTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryLetterTemplate extends Model
{
    protected $table = 't_HRDisciplinaryLetterTemplates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'LetterType',
        'TemplateID',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(LegalTemplate::class, 'TemplateID');
    }
}
