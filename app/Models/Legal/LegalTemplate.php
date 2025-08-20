<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalTemplate extends Model
{
    protected $table = 't_LegalTemplates';

    protected $fillable = [
        'TemplateName',
        'DocumentType',
        'Version',
        'Description',
        'DMSDocID',
        'CreatedBy',
        'ModifiedBy',
        'IsActive',
    ];

    public $timestamps = false;

    protected static function booted()
    {
        static::creating(function ($template) {
            $template->CreatedBy = auth()->id();
            $template->CreatedOn = now();
        });

        static::updating(function ($template) {
            $template->ModifiedBy = auth()->id();
            $template->ModifiedOn = now();
        });
    }
}
