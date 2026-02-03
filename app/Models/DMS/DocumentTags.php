<?php

namespace App\Models\DMS;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTags extends Pivot
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentTags';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'DocId', 'TagId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentTag';
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(DMSTags::class, 'TagId', 'Id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocId');
    }
}
