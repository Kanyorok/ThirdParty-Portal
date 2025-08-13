<?php

namespace App\Models\DMS;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentLegalHold extends Pivot
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentLegalHolds';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'DocId', 'LegalHoldId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentLegalHoldId';
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocId');
    }

    public function hold(): BelongsTo
    {
        return $this->belongsTo(LegalHold::class, 'LegalHoldId');
    }
}
