<?php

namespace App\Models\Procurement;

use App\Enums\Core\PostingEnum;
use App\Models\Auth\User;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\StockTransaction;
use App\Models\ThirdParies\Supplier;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnhancedGoodsReceipt extends Model
{
    use UserActorTrait;
    use SoftDeletes;

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';

    protected $table = 't_GoodsReceipts';
    protected $primaryKey = 'id';

    protected $fillable = [
        // Original fields
        'GRNID', 'POID', 'ReceivedDate', 'SupplierId', 'StoreID', 'ReceivedBy',
        'InspectionStatus', 'TransferStatus', 'ItemNo', 'POQTY', 'ReceivedQTY',
        'TransferTo', 'TagRequired', 'CreatedBy', 'ModifiedBy', 'DeletedBy',

        // Enhanced fields
        'OrderLineID', 'UnitPrice', 'TotalValue', 'ItemType', 'ProcessingStatus',
        'UpdatedStock', 'StockTransactionRef', 'CreatedJournalEntry', 'JournalEntryRef',
        'DebitAmount', 'CreditAmount', 'AssetRegisterRef', 'RequiresAssetTagging',
        'QualityStatus', 'QualityRemarks', 'QualityCheckedAt', 'QualityCheckedBy',
        'DeliveryNoteRef', 'BatchNumber', 'ExpiryDate', 'ManufactureDate',
        'ProcessingErrors', 'PostedAt', 'PostedBy',
    ];

    protected $casts = [
        'InspectionStatus' => PostingEnum::class,
        'ReceivedDate' => 'datetime',
        'ExpiryDate' => 'date',
        'ManufactureDate' => 'date',
        'QualityCheckedAt' => 'datetime',
        'PostedAt' => 'datetime',
        'TagRequired' => 'boolean',
        'UpdatedStock' => 'boolean',
        'CreatedJournalEntry' => 'boolean',
        'RequiresAssetTagging' => 'boolean',
        'UnitPrice' => 'decimal:2',
        'TotalValue' => 'decimal:2',
        'DebitAmount' => 'decimal:2',
        'CreditAmount' => 'decimal:2',
        'POQTY' => 'decimal:2',
        'ReceivedQTY' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    // Item type constants
    public const ITEM_TYPE_STOCK = 'stock';
    public const ITEM_TYPE_ASSET = 'asset';
    public const ITEM_TYPE_SERVICE = 'service';

    // Processing status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_ERROR = 'error';

    // Quality status constants
    public const QUALITY_PENDING = 'pending';
    public const QUALITY_PASSED = 'passed';
    public const QUALITY_FAILED = 'failed';
    public const QUALITY_NOT_REQUIRED = 'not_required';

    public static function getPrimaryKey(): string
    {
        return 'id';
    }

    // Relationships
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ReceivedBy', 'Id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'SupplierId');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemMasterList::class, 'ItemNo', 'Id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'POID', 'Id');
    }

    public function orderLine(): BelongsTo
    {
        return $this->belongsTo(OrderLines::class, 'OrderLineID', 'Id');
    }

    public function qualityChecker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'QualityCheckedBy', 'Id');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'PostedBy', 'Id');
    }

    public function stockTransaction(): HasOne
    {
        return $this->hasOne(StockTransaction::class, 'ReferenceID', 'id')
            ->where('TransactionType', 'GRN');
    }

    public function journalEntry(): HasOne
    {
        return $this->hasOne(FinanceJournalEntry::class, 'RefNo', 'JournalEntryRef');
    }

    // Accessor methods
    public function getProcessingStatusBadgeAttribute(): array
    {
        $badges = [
            self::STATUS_PENDING => ['text' => 'Pending', 'class' => 'warning'],
            self::STATUS_PROCESSED => ['text' => 'Processed', 'class' => 'success'],
            self::STATUS_ERROR => ['text' => 'Error', 'class' => 'danger'],
        ];

        return $badges[$this->ProcessingStatus] ?? ['text' => 'Unknown', 'class' => 'secondary'];
    }

    public function getQualityStatusBadgeAttribute(): array
    {
        $badges = [
            self::QUALITY_PENDING => ['text' => 'Pending', 'class' => 'warning'],
            self::QUALITY_PASSED => ['text' => 'Passed', 'class' => 'success'],
            self::QUALITY_FAILED => ['text' => 'Failed', 'class' => 'danger'],
            self::QUALITY_NOT_REQUIRED => ['text' => 'Not Required', 'class' => 'info'],
        ];

        return $badges[$this->QualityStatus] ?? ['text' => 'Unknown', 'class' => 'secondary'];
    }

    public function getItemTypeDisplayAttribute(): string
    {
        $displays = [
            self::ITEM_TYPE_STOCK => 'Stock Item',
            self::ITEM_TYPE_ASSET => 'Asset Item',
            self::ITEM_TYPE_SERVICE => 'Service Item',
        ];

        return $displays[$this->ItemType] ?? 'Unknown';
    }

    // Helper methods
    public function isStock(): bool
    {
        return $this->ItemType === self::ITEM_TYPE_STOCK;
    }

    public function isAsset(): bool
    {
        return $this->ItemType === self::ITEM_TYPE_ASSET;
    }

    public function isService(): bool
    {
        return $this->ItemType === self::ITEM_TYPE_SERVICE;
    }

    public function isPending(): bool
    {
        return $this->ProcessingStatus === self::STATUS_PENDING;
    }

    public function isProcessed(): bool
    {
        return $this->ProcessingStatus === self::STATUS_PROCESSED;
    }

    public function hasError(): bool
    {
        return $this->ProcessingStatus === self::STATUS_ERROR;
    }

    public function isQualityPassed(): bool
    {
        return $this->QualityStatus === self::QUALITY_PASSED;
    }

    public function isQualityFailed(): bool
    {
        return $this->QualityStatus === self::QUALITY_FAILED;
    }

    public function requiresQualityCheck(): bool
    {
        return $this->QualityStatus !== self::QUALITY_NOT_REQUIRED;
    }

    public function canBePosted(): bool
    {
        return $this->InspectionStatus === PostingEnum::Draft
            && ($this->isQualityPassed() || $this->QualityStatus === self::QUALITY_NOT_REQUIRED)
            && $this->ReceivedQTY > 0;
    }

    public function isPosted(): bool
    {
        return $this->InspectionStatus === PostingEnum::Posted;
    }

    // Business logic methods
    public function markAsProcessed(string $processType = null): self
    {
        $this->update([
            'ProcessingStatus' => self::STATUS_PROCESSED,
            'PostedAt' => now(),
            'PostedBy' => auth()->id(),
        ]);

        return $this;
    }

    public function markAsError(string $error): self
    {
        $this->update([
            'ProcessingStatus' => self::STATUS_ERROR,
            'ProcessingErrors' => $error,
        ]);

        return $this;
    }

    public function markQualityPassed(string $remarks = null): self
    {
        $this->update([
            'QualityStatus' => self::QUALITY_PASSED,
            'QualityRemarks' => $remarks,
            'QualityCheckedAt' => now(),
            'QualityCheckedBy' => auth()->id(),
        ]);

        return $this;
    }

    public function markQualityFailed(string $remarks): self
    {
        $this->update([
            'QualityStatus' => self::QUALITY_FAILED,
            'QualityRemarks' => $remarks,
            'QualityCheckedAt' => now(),
            'QualityCheckedBy' => auth()->id(),
        ]);

        return $this;
    }

    // Scope methods
    public function scopePendingProcessing($query)
    {
        return $query->where('ProcessingStatus', self::STATUS_PENDING);
    }

    public function scopeProcessed($query)
    {
        return $query->where('ProcessingStatus', self::STATUS_PROCESSED);
    }

    public function scopeByItemType($query, string $itemType)
    {
        return $query->where('ItemType', $itemType);
    }

    public function scopeReadyForPosting($query)
    {
        return $query->where('InspectionStatus', PostingEnum::Draft)
            ->where(function ($q) {
                $q->where('QualityStatus', self::QUALITY_PASSED)
                    ->orWhere('QualityStatus', self::QUALITY_NOT_REQUIRED);
            })
            ->where('ReceivedQTY', '>', 0);
    }

    public function scopeByGRN($query, string $grnId)
    {
        return $query->where('GRNID', $grnId);
    }

    public function scopeByPO($query, string $poId)
    {
        return $query->where('POID', $poId);
    }
}
