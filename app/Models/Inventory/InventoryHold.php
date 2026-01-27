<?php
namespace App\Models\Inventory;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\Core\Approval\CodeDetail;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Inventory\StockAdjustment;
use App\Models\Inventory\TransactionReceipt;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\Store;

class InventoryHold extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_InventoryHold';
    protected $primaryKey = 'Id';
    protected $connection = 'sqlsrv';

    protected $fillable = [
        'InventoryHoldID', 'ItemID', 'BranchID', 'Store', 'Quantity', 'Reason', 'Source', 'SourceID',
        'Status', 'Remarks', 'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy'
    ];

    public function defect()
    {
        return $this->belongsTo(CodeDetail::class, 'Reason', 'ID')
            ->where('CodeID', 'Adjustment Reason');
    }

    public function defectDetail()
    {
        return $this->belongsTo(CodeDetail::class, 'Reason', 'ID');
    }

    public function item()
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemID');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'CreatedBy', 'Id');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'ModifiedBy', 'Id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'DeletedBy', 'Id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID');
    }

    /**
     * For transfer scenarios, this represents the branch the item came from
     * In most cases, this will be the same as 'branch' unless dealing with transfers
     * This is added for compatibility with the blade template
     */
    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'BranchID');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'Store');
    }

    public function sourceDetail()
    {
        return $this->belongsTo(CodeDetail::class, 'Source');
    }

    /**
     * Get the source adjustment if source is Stock Adjustment
     */
    public function sourceAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'SourceID', 'Id');
    }

    /**
     * Get the source receipt if source is Transfer Receipt or other receipt types
     */
    public function sourceReceipt()
    {
        return $this->belongsTo(TransactionReceipt::class, 'SourceID', 'Id');
    }

    /**
     * Get the formatted source document ID based on source type
     * This returns the AdjustmentId or ReceiptId column values
     */
    public function getSourceDocumentIdAttribute()
    {
        $sourceType = $this->sourceDetail->Description ?? '';
        
        // For Stock Adjustments, get AdjustmentId from StockAdjustment table
        if (stripos($sourceType, 'adjustment') !== false && $this->sourceAdjustment) {
            return $this->sourceAdjustment->AdjustmentId ?? $this->SourceID;
        } 
        // For Receipts/Transfers, get ReceiptId from TransactionReceipt table
        elseif ((stripos($sourceType, 'receipt') !== false || stripos($sourceType, 'transfer') !== false) && $this->sourceReceipt) {
            return $this->sourceReceipt->ReceiptId ?? $this->SourceID;
        }
        
        // Fallback to the generic SourceID if no relationship found
        return $this->SourceID;
    }

    public function inventoryHoldReview()
    {
        return $this->hasOne(InventoryHoldReview::class, 'InventoryHoldID', 'Id');
    }

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    protected static function booted()
    {
        static::created(function ($hold) {
            if (!$hold->InventoryHoldID) {
                $year = now()->format('Y');
                $hold->newQueryWithoutScopes()
                    ->where('Id', $hold->Id)
                    ->update([
                        'InventoryHoldID' => 'HLD-' . $year . '-' . str_pad($hold->Id, 4, '0', STR_PAD_LEFT)
                    ]);
                $hold->InventoryHoldID = 'HLD-' . $year . '-' . str_pad($hold->Id, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}