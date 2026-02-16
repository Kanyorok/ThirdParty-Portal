<?php

namespace App\Models\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Models\Auth\User;
use App\Traits\Model\SpecialPermissionTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentValidation extends Model
{
    use SoftDeletes;
    use UserActorTrait;
    use SpecialPermissionTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentValidations';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "Name", "ValidationId", "Visibility", "ValidationTypeId", "DocumentId", "ApprovedBy", "ApprovedOn",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        // 'Type' => DocumentValidationTypeEnum::class,
        'Visibility' => VisibilityEnum::class,
        'ApprovedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentValidationId';
    }

    public function getRouteKeyName(): string
    {
        return 'ValidationId';
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentValidationType::class, 'ValidationTypeId', 'Id')->withTrashed();
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'DocumentId');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(DocumentValidationAttribute::class, 'DocumentValidationId');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ApprovedBy', 'Id')->withTrashed();
    }
}
