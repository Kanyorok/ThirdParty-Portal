<?php

namespace App\Models\DMS;

use App\Enums\Core\DataTypesEnum;
use App\Services\DMS\Files\FileProperties;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentValidationAttribute extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_DocumentValidationAttributes';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "Name", "Value", "DataType", "DocumentValidationId",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'DataType' => DataTypesEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentValidationAttributeId';
    }

    public function getFormatedValueAttribute(): string
    {
        return match ($this->DataType->value) {
            DataTypesEnum::DateTime->value => $this->DataType->val($this->Value, FileProperties::DATE_TIME_FORMAT),
            DataTypesEnum::Time->value => $this->DataType->val($this->Value, FileProperties::TIME_FORMAT),
            default => $this->DataType->val($this->Value)
        };
    }
}
