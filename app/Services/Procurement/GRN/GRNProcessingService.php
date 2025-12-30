<?php

namespace App\Services\Procurement\GRN;

use App\Models\Procurement\EnhancedGoodsReceipt;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\StockItem;
use App\Models\Finance\FinanceJournalEntry;
use App\Models\Finance\FinanceJournalLines;
use App\Models\Finance\FinanceGLMapping;
use App\Models\Inventory\ItemMasterList;
use App\Services\Finance\JournalEntryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GRNProcessingService
{
    protected $journalService;

    public function __construct(JournalEntryService $journalService = null)
    {
        // Create a basic journal service if not injected
        $this->journalService = $journalService ?? new class {
            public function createJournalEntry(array $data)
            {
                return FinanceJournalEntry::create($data);
            }

            public function addJournalLine($journalEntry, array $lineData)
            {
                return FinanceJournalLines::create([
                    'JournalEntryId' => is_object($journalEntry) ? $journalEntry->Id : $journalEntry,
                    ...$lineData
                ]);
            }
        };
    }

    /**
     * Process a single GRN line based on item type
     */
    public function processGRNLine(EnhancedGoodsReceipt $grnLine): bool
    {
        DB::beginTransaction();

        try {
            // Determine item type if not set
            if (!$grnLine->ItemType) {
                $itemType = $this->determineItemType($grnLine);
                $grnLine->update(['ItemType' => $itemType]);
            }

            // Process based on item type
            switch ($grnLine->ItemType) {
                case EnhancedGoodsReceipt::ITEM_TYPE_STOCK:
                    $this->processStockItem($grnLine);
                    break;

                case EnhancedGoodsReceipt::ITEM_TYPE_ASSET:
                    $this->processAssetItem($grnLine);
                    break;

                case EnhancedGoodsReceipt::ITEM_TYPE_SERVICE:
                    $this->processServiceItem($grnLine);
                    break;

                default:
                    throw new Exception("Unknown item type: {$grnLine->ItemType}");
            }

            // Create journal entry for all types
            $this->createJournalEntry($grnLine);

            // Mark as processed
            $grnLine->markAsProcessed();

            DB::commit();



            return true;
        } catch (Exception $e) {
            DB::rollBack();

            $error = "Failed to process GRN line: " . $e->getMessage();
            $grnLine->markAsError($error);

            Log::error("GRN processing failed", [
                'grn_id' => $grnLine->GRNID,
                'item_no' => $grnLine->ItemNo,
                'error' => $error,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Process stock item - update inventory
     */
    protected function processStockItem(EnhancedGoodsReceipt $grnLine): void
    {
        $item = ItemMasterList::find($grnLine->ItemNo);
        if (!$item) {
            throw new Exception("Item not found: {$grnLine->ItemNo}");
        }

        // Create or update stock item
        // Convert store ID to integer if needed
        $storeId = is_numeric($grnLine->StoreID) ? (int)$grnLine->StoreID : 1;

        $stockItem = StockItem::updateOrCreate(
            [
                'ItemID' => $grnLine->ItemNo,
                'Store' => $storeId,
                'Branch' => 1, // Default branch - should be configurable
            ],
            [
                'SKUCode' => $item->ItemCode ? $item->ItemCode : "SKU-{$grnLine->ItemNo}",
                'UOM' => $item->UOM,
                'UnitCost' => $grnLine->UnitPrice,
                'CurrentQty' => 0, // Initialize with 0, will be incremented
                'Min' => 0, // Minimum stock level
                'Reorder' => 0, // Reorder level
                'Max' => 9999, // Maximum stock level
                'LastReceived' => now(),
                'Status' => true,
                'CreatedBy' => auth()->id() ?? 1,
                'ModifiedBy' => auth()->id() ?? 1,
            ]
        );

        // Update current quantity
        $stockItem->increment('CurrentQty', $grnLine->ReceivedQTY);

        // Create stock transaction
        // Get or create transaction type for GRN
        $transactionTypeId = DB::table('t_FinanceTransactionTypes')->where('Code', 'GRN-STOCK')->value('Id') ?? 1;

        $stockTransaction = StockTransaction::create([
            'SKUID' => $stockItem->SKUCode,
            'TransactionType' => $transactionTypeId,
            'ReferenceID' => $grnLine->GRNID, // Use GRNID as reference instead of internal ID
            'ItemID' => $grnLine->ItemNo,
            'StoreID' => $storeId,
            'BranchID' => 1, // Default branch
            'UnitCost' => $grnLine->UnitPrice,
            'UOMID' => $item->UOM,
            'QuantityIn' => $grnLine->ReceivedQTY,
            'QuantityOut' => 0,
            'BalanceQty' => $stockItem->CurrentQty,
            'TotalCost' => $grnLine->TotalValue,
            'TransactionDate' => $grnLine->ReceivedDate,
            'Remarks' => "GRN Receipt - {$grnLine->GRNID}",
            'CreatedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        // Update GRN line
        $grnLine->update([
            'UpdatedStock' => true,
            'StockTransactionRef' => $stockTransaction->Id,
        ]);
    }

    /**
     * Process asset item - create asset register entry (placeholder for now)
     */
    protected function processAssetItem(EnhancedGoodsReceipt $grnLine): void
    {
        // TODO: Implement asset register creation when asset module is ready
        // For now, just mark that it requires asset tagging

        $assetRef = "ASSET-" . $grnLine->GRNID . "-" . $grnLine->ItemNo;

        $grnLine->update([
            'AssetRegisterRef' => $assetRef,
            'RequiresAssetTagging' => true,
        ]);
    }

    /**
     * Process service item - direct expense (no inventory update needed)
     */
    protected function processServiceItem(EnhancedGoodsReceipt $grnLine): void
    {
        // Services don't need inventory updates, just journal entry
        // The journal entry will be created in the main process method


    }

    /**
     * Create journal entry for GRN based on item type
     */
    protected function createJournalEntry(EnhancedGoodsReceipt $grnLine): void
    {
        // Get GL mapping based on item type
        $transactionCode = $this->getTransactionCode($grnLine->ItemType);
        $glMapping = $this->getGLMapping($transactionCode);

        if (!$glMapping) {
            throw new Exception("GL mapping not found for transaction code: {$transactionCode}");
        }

        // Create journal entry
        // Get transaction type ID instead of code to avoid constraint issues
        $transactionType = DB::table('t_FinanceTransactionTypes')->where('Code', $transactionCode)->first();
        $transactionTypeId = $transactionType ? $transactionType->Id : 1; // Default fallback

        $journalEntry = $this->journalService->createJournalEntry([
            'Date' => $grnLine->ReceivedDate,
            'Type' => 'normal', // Use allowed constraint value
            'Description' => "GRN - {$grnLine->GRNID} - " . $this->getItemTypeDisplay($grnLine->ItemType),
            'SystemDescription' => "Auto-generated from GRN processing",
            'Status' => 'Posted', // Auto-post GRN entries
            'CurrencyID' => 1, // Default currency
            'CreatedBy' => auth()->id() ?? 1,
            'ModifiedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        // Create debit line (Asset/Expense account)
        $itemName = $grnLine->item ? $grnLine->item->ItemName : 'Item #' . $grnLine->ItemNo;
        $this->journalService->addJournalLine($journalEntry, [
            'GLAccountID' => $glMapping->DebitGLAccountID,
            'Debit' => $grnLine->TotalValue,
            'Credit' => 0,
            'Amount' => $grnLine->TotalValue,
            'IsDebit' => true,
            'Narration' => "GRN Receipt - {$itemName}",
            'SystemDescription' => "Debit for GRN {$grnLine->GRNID}",
            'CreatedBy' => auth()->id() ?? 1,
            'ModifiedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        // Create credit line (Accrued payable account)
        $supplierName = $grnLine->supplier && $grnLine->supplier->TradingName ? $grnLine->supplier->TradingName : 'Supplier';
        $this->journalService->addJournalLine($journalEntry, [
            'GLAccountID' => $glMapping->CreditGLAccountID,
            'Debit' => 0,
            'Credit' => $grnLine->TotalValue,
            'Amount' => $grnLine->TotalValue,
            'IsDebit' => false,
            'Narration' => "GRN Accrual - {$supplierName}",
            'SystemDescription' => "Credit for GRN {$grnLine->GRNID}",
            'CreatedBy' => auth()->id() ?? 1,
            'ModifiedBy' => auth()->id() ?? 1,
            'CreatedOn' => now(),
        ]);

        // Update GRN line
        $grnLine->update([
            'CreatedJournalEntry' => true,
            'JournalEntryRef' => $journalEntry->RefNo,
            'DebitAmount' => $grnLine->TotalValue,
            'CreditAmount' => $grnLine->TotalValue,
        ]);
    }

    /**
     * Determine item type based on item master data
     */
    protected function determineItemType(EnhancedGoodsReceipt $grnLine): string
    {
        $item = ItemMasterList::with('itemType')->find($grnLine->ItemNo);

        if (!$item) {
            Log::warning("Item not found, defaulting to stock", ['item_no' => $grnLine->ItemNo]);
            return EnhancedGoodsReceipt::ITEM_TYPE_STOCK;
        }

        // Map item type from master data
        // This assumes itemType relation has a field that indicates the type
        $itemTypeName = ($item->itemType && $item->itemType->TypeName) ? $item->itemType->TypeName : 'Stock';

        $typeMapping = [
            'Stock' => EnhancedGoodsReceipt::ITEM_TYPE_STOCK,
            'Inventory' => EnhancedGoodsReceipt::ITEM_TYPE_STOCK,
            'Asset' => EnhancedGoodsReceipt::ITEM_TYPE_ASSET,
            'Fixed Asset' => EnhancedGoodsReceipt::ITEM_TYPE_ASSET,
            'Service' => EnhancedGoodsReceipt::ITEM_TYPE_SERVICE,
            'Non-Stock' => EnhancedGoodsReceipt::ITEM_TYPE_SERVICE,
        ];

        return $typeMapping[$itemTypeName] ?? EnhancedGoodsReceipt::ITEM_TYPE_STOCK;
    }

    /**
     * Get transaction code based on item type
     */
    protected function getTransactionCode(string $itemType): string
    {
        $codes = [
            EnhancedGoodsReceipt::ITEM_TYPE_STOCK => 'GRN-STOCK',
            EnhancedGoodsReceipt::ITEM_TYPE_ASSET => 'GRN-ASSET',
            EnhancedGoodsReceipt::ITEM_TYPE_SERVICE => 'GRN-SERVICE',
        ];

        return $codes[$itemType] ?? 'GRN-STOCK';
    }

    /**
     * Get GL mapping for transaction code
     */
    protected function getGLMapping(string $transactionCode): ?FinanceGLMapping
    {
        return FinanceGLMapping::with(['debitAccount', 'creditAccount'])
            ->whereHas('transactions', function ($query) use ($transactionCode) {
                $query->where('Code', $transactionCode);
            })
            ->where('IsActive', true)
            ->first();
    }

    /**
     * Get display name for item type
     */
    protected function getItemTypeDisplay(string $itemType): string
    {
        $displays = [
            EnhancedGoodsReceipt::ITEM_TYPE_STOCK => 'Stock Item',
            EnhancedGoodsReceipt::ITEM_TYPE_ASSET => 'Asset Item',
            EnhancedGoodsReceipt::ITEM_TYPE_SERVICE => 'Service Item',
        ];

        return $displays[$itemType] ?? 'Unknown Item Type';
    }

    /**
     * Process entire GRN (all lines)
     */
    public function processGRN(string $grnId): array
    {
        $grnLines = EnhancedGoodsReceipt::byGRN($grnId)
            ->readyForPosting()
            ->get();

        if ($grnLines->isEmpty()) {
            throw new Exception("No GRN lines found ready for posting for GRN: {$grnId}");
        }

        $results = [
            'total' => $grnLines->count(),
            'processed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($grnLines as $grnLine) {
            if ($this->processGRNLine($grnLine)) {
                $results['processed']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'item_no' => $grnLine->ItemNo,
                    'error' => $grnLine->ProcessingErrors,
                ];
            }
        }

        return $results;
    }

    /**
     * Validate GRN line before processing
     */
    public function validateGRNLine(EnhancedGoodsReceipt $grnLine): array
    {
        $errors = [];

        if (!$grnLine->ItemNo) {
            $errors[] = "Item number is required";
        }

        if (!$grnLine->ReceivedQTY || $grnLine->ReceivedQTY <= 0) {
            $errors[] = "Received quantity must be greater than zero";
        }

        if (!$grnLine->UnitPrice || $grnLine->UnitPrice < 0) {
            $errors[] = "Unit price must be zero or greater";
        }

        if (!$grnLine->SupplierId) {
            $errors[] = "Supplier is required";
        }

        if (!$grnLine->StoreID) {
            $errors[] = "Store is required";
        }

        // Check if item exists
        if ($grnLine->ItemNo && !ItemMasterList::find($grnLine->ItemNo)) {
            $errors[] = "Item not found in master list";
        }

        return $errors;
    }
}
