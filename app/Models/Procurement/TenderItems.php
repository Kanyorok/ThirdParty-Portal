<?php

namespace App\Models\Procurement;

use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderItems extends Model
{
    protected $table = 't_TenderItems';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'TenderID',
        'SourceType',
        'ItemID',
        'PlanItemID',
        'ManualItemDescription',
        'PlannedQty',
        'QtyToTender',
        'ItemCategory',
        'Remarks',
        'RelatedPRID',
        'CreatedBy',
        'ModifiedBy',
    ];

    // Relationships
    public function tender()
    {
        return $this->belongsTo(Tender::class, 'TenderID');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID');
    }

    public function category()
    {
        return $this->belongsTo(ItemCategories::class, 'ItemCategory', 'Id');
    }

    public function planLineItem(): BelongsTo
    {
        return $this->belongsTo(PlanLineItem::class, 'PlanItemID', 'LineItemID');
    }
}
