<?php

namespace App\Models\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Traits\Model\SpecialPermissionTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentValidationType extends Model
{
    use SoftDeletes, UserActorTrait, SpecialPermissionTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

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
}
