<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalDraft extends Model
{
    protected $table = 't_LegalDrafts';

    protected $fillable = [
        'DraftTitle',
        'Description',
        'DocumentType',
        'Content',
        'Status',
        'LinkedTemplateID',
        'CreatedBy',
        'ModifiedBy',
        'IsActive',
    ];

    public $timestamps = false;

    protected static function booted()
    {
        static::creating(function ($draft) {
            $draft->CreatedBy = auth()->id();
            $draft->CreatedOn = now();
        });

        static::updating(function ($draft) {
            $draft->ModifiedBy = auth()->id();
            $draft->ModifiedOn = now();
        });
    }
}
