<?php

namespace App\Models;

use App\Enums\ItemTypeEnum;
use App\Enums\TonalityEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DescriptionItem extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $table = 't_DescriptionItems';
    protected $primaryKey = 'Id';

    protected $fillable = [
        "Item", "ItemID", "Description", "ItemType", "Tonality",
        'Notes', 'CreatedBy', 'ModifiedBy', 'DeletedBy'
    ];


    protected $casts = [
        'ItemType' => ItemTypeEnum::class,
        'Tonality' => TonalityEnum::class,
    ];

    public function item(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, "Item", "ItemID");
    }
}
