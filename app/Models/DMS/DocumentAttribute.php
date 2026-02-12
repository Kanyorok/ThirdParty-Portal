<?php

namespace App\Models\DMS;

use App\Enums\Core\DataTypesEnum;
use App\Services\DMS\Files\FileProperties;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentAttribute extends Model
{
    use SoftDeletes, UserActorTrait;

    public const string CREATED_AT = 'CreatedOn';
    public const string UPDATED_AT = 'ModifiedOn';
    public const string DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentAttributes';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'DocumentId', 'Name', 'Value', 'DataType', 'VersionId',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'DataType' => DataTypesEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentAttributeId';
    }

    public function getFormatedValueAttribute(): string
    {
        return match ($this->DataType->value) {
            DataTypesEnum::DateTime->value => $this->DataType->val($this->Value, FileProperties::DATE_TIME_FORMAT),
            DataTypesEnum::Time->value => $this->DataType->val($this->Value, FileProperties::TIME_FORMAT),
            default => $this->DataType->val($this->Value)
        };
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class, 'VersionId');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }
}
