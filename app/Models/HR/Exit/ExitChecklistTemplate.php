<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExitChecklistTemplate extends Model
{
    protected $table = 't_HRExitChecklistTemplates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Description',
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

    public function items(): HasMany
    {
        return $this->hasMany(ExitChecklistItem::class, 'TemplateID');
    }
}
