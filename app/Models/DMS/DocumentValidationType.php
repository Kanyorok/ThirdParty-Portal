<?php

namespace App\Models\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Interfaces\SpecialPermissionContract;
use App\Traits\Model\SpecialPermissionTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentValidationType extends Model implements SpecialPermissionContract
{
    use SoftDeletes;
    use UserActorTrait;
    use SpecialPermissionTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentValidationTypes';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "ValidationTypeId", "Name", "Notes", "Visibility",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        // 'Type' => DocumentValidationTypeEnum::class,
        'Visibility' => VisibilityEnum::class,
        'ApprovedOn' => 'datetime',
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentValidationTypeId';
    }

    public function getRouteKeyName(): string
    {
        return 'ValidationTypeId';
    }

    public function validations(): HasMany
    {
        return $this->hasMany(DocumentValidation::class, 'ValidationTypeId', 'Id');
    }

    public function getShareEmailSubject(): string
    {
        return 'Notification: Added as default document validator to  #' . $this->ValidationTypeId;
    }

    public function getSharedName(): string
    {
        return "#" . $this->ValidationTypeId;
    }
}
