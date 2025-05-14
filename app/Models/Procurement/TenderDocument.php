<?php

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderDocument extends Model{
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    
    protected $table = 't_TenderDocument';
    protected $primaryKey = 'TenderDocumentID';
    protected $keyType = 'integer';
    public $incrementing = false;

    protected $fillable = [
        'TenderID',
        'FilePath',
        'Description',
        'CreatedBy',
        'ModifiedBy',
    ];

    protected $dates = [
        'CreatedOn' => 'date:Y-m-d',
        'ModifiedOn' => 'date:Y-m-d',
    ];

    protected $casts = [
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'TenderID' => 'integer',
    ];

    public function tender(): BelongsTo {
        return $this->belongsTo(Tender::class, 'TenderID', 'TenderID');
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'CreatedBy', 'UserID');
    }

    public function modifier(): BelongsTo {
        return $this->belongsTo(User::class, 'ModifiedBy', 'UserID');
    }

    public function deleter(): BelongsTo {
        return $this->belongsTo(User::class, 'DeletedBy', 'UserID');
    }

    /**
     * Get the document's download URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('tender.documents.download', [
            'tender' => $this->TenderID,
            'document' => $this->TenderDocumentID
        ]);
    }

    /**
     * Get the original filename from storage path
     */
    public function getOriginalFilenameAttribute(): string
    {
        return basename($this->FilePath);
    }
}