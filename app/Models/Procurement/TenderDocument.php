<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderDocument extends Model{
    protected $table = 't_TenderDocuments';
    protected $primaryKey = 'TenderDocumentID';
    protected $keyType = 'integer';
    public $incrementing = true;

    protected $fillable = [
        'TenderID',
        'FilePath',
        'Description',
    ];

    public function tender(): BelongsTo {
        return $this->belongsTo(Tender::class, 'TenderID');
    }
}
