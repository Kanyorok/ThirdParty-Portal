<?php

namespace App\Models\CRM;

use App\Enums\ItemTypeEnum;
use App\Enums\TonalityEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DescriptionItem extends Model
{
    use SoftDeletes;
    use UserActorTrait;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_DescriptionItems';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "Item",
        "ItemID",
        "Description",
        "ItemType",
        "Tonality",
        'Notes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];


    protected $casts = [
        'ItemType' => ItemTypeEnum::class,
        'Tonality' => TonalityEnum::class,
    ];

    public static function getPrimaryKey(): string
    {
        return 'DescriptionItemsId';
    }

    public function item(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Item", "ItemID");
    }
}
