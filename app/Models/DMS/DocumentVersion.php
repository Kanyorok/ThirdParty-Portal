<?php

namespace App\Models\DMS;

use App\Enums\DMS\DisksEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentVersion extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const string CREATED_AT = 'CreatedOn';
    public const string UPDATED_AT = 'ModifiedOn';
    public const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentVersions';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'DocumentId', "Name", "Version", "Path", "Disk", "Checksum", "Size", "Description", "Blob",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Size' => 'integer',
        'Disk' => DisksEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentVersionId';
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(DocumentAttribute::class, 'VersionId');
    }
}
