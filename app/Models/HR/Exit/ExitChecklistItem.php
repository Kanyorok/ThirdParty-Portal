<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitChecklistItem extends Model
{
    protected $table = 't_HRExitChecklistItems';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'TemplateID',
        'ClearanceDepartmentID',
        'ItemName',
        'Sequence',
        'IsMandatory',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsMandatory' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ExitChecklistTemplate::class, 'TemplateID');
    }

    public function clearanceDepartment(): BelongsTo
    {
        return $this->belongsTo(ExitClearanceDepartment::class, 'ClearanceDepartmentID');
    }
}
