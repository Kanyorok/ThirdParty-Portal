<?php

namespace App\Models\DMS;

use App\Enums\DMS\ContentEnum;
use App\Enums\DMS\StringComparisonEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTaggingRules extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_DMSTaggingRules';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "TagId", "Content", "Comparison", "Value",
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $casts = [
        'Content' => ContentEnum::class,
        'Comparison' => StringComparisonEnum::class
    ];

    public static function getPrimaryKey(): string
    {
        return 'DocumentTaggingRulesId';
    }
}
