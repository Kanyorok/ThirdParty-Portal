<?php

namespace App\Models\DMS;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentSignature extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentSignatures';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "DocumentId", "SignatureId", "Extra", "Content",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Extra' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId', 'Id');
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(DMSSignature::class, 'SignatureId', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'DocumentSignatureId';
    }
}
