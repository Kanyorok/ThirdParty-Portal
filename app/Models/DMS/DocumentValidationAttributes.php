<?php

namespace App\Models\DMS;

use App\Enums\Core\DataTypesEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentValidationAttributes extends Model
{
    use SoftDeletes, UserActorTrait;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

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
}
