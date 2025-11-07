<?php

namespace App\Models\Procurement;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderLines extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $connection = 'sqlsrv';
    protected $table = 't_OrderLines';
    protected $primaryKey = 'Id';

    public static function getPrimaryKey(): string
    {
        return 'OrderLineID';
    }

    protected $fillable = [
        'iOrderID',        // Actual column name for OrderID
        'cDescription',    // Actual column name for ItemDescription
        'fQuantity',       // Actual column name for Quantity
        'UnitOfMeasure',   // New column we're adding
        'fUnitPriceExcl',  // Actual column name for UnitPrice
        'TaxPercentage',   // New column we're adding
        'DiscountPercentage', // New column we're adding
        'LineTotal',       // Actual column name for TotalAmount
        'iStockCodeID',    // Actual column name for ItemID
        'cLineNotes',      // Actual column name for Notes
        'CreatedBy',
        'ModifiedBy'
    ];

    protected $casts = [
        'fQuantity' => 'decimal:2',
        'fUnitPriceExcl' => 'decimal:2',
        'TaxPercentage' => 'decimal:2',
        'DiscountPercentage' => 'decimal:2',
        'LineTotal' => 'decimal:2',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'iOrderID' => 'integer',
        'iStockCodeID' => 'integer'
    ];

    /**
     * Get the order that owns this line
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'iOrderID', 'Id');
    }

    /**
     * Get the item if linked to master item list
     */
    public function item()
    {
        return $this->belongsTo(\App\Models\Inventory\ItemMasterList::class, 'iStockCodeID', 'Id');
    }

    /**
     * Calculate line total with tax and discount
     */
    public function calculateLineTotal(): float
    {
        $subtotal = $this->fQuantity * $this->fUnitPriceExcl;
        $taxAmount = $subtotal * ($this->TaxPercentage / 100);
        $discountAmount = $subtotal * ($this->DiscountPercentage / 100);

        return $subtotal + $taxAmount - $discountAmount;
    }

    /**
     * Get formatted line total
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->LineTotal, 2);
    }
}
