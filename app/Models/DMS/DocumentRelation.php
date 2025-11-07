<?php

namespace App\Models\DMS;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentRelation extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentRelations';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "DocumentId", "Related", "RelatedID",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentRelationId';
    }

    public function related(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Related", "RelatedID");
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId', 'Id');
    }
}
