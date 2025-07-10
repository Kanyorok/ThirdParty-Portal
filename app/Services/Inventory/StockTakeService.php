<?php

namespace App\Services\Inventory;

use App\Models\Core\Branch;
use App\Models\Inventory\StockTake;
use App\Models\Inventory\StockTakeLines;
use App\Models\Inventory\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockTakeService
{
    protected StockTake $stockTake;

    private function __construct(StockTake $stockTake)
    {
        $this->stockTake = $stockTake;
    }

    /**
     * Creates a StockTake and associated lines in a single transaction
     */
    public static function createWithLines(
        Branch $branch,
        Store  $store,
        string $countedBy,
        Carbon $countDate,
        array  $lines
    ): self
    {
        return DB::transaction(function () use ($branch, $store, $countedBy, $countDate, $lines) {
            $service = self::create($branch, $store, $countedBy, $countDate);

            foreach ($lines as $line) {
                $service->addLine(
                    itemId: $line['ItemId'],
                    actualQuantity: $line['ActualQuantity'],
                    countedQuantity: $line['CountedQuantity'],
                    remarks: $line['Remarks'] ?? null
                );
            }

            return $service;
        });
    }

    /**
     * Creates StockTake record and returns service instance
     */
    public static function create(
        Branch $branch,
        Store $store,
        string $countedBy,
        Carbon $countDate
    ): self
    {
        return DB::transaction(function () use ($branch, $store, $countedBy, $countDate) {
            $stockTake = StockTake::create([
                'BranchId' => $branch->Id,
                'StoreId' => $store->Id,
                'CountedBy' => $countedBy,
                'CountDate' => $countDate,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($stockTake)
                ->event('create')
                ->log("Added Stock Take #{$stockTake->Id}");

            return new self($stockTake);
        });
    }

    /**
     * Adds a line to the current StockTake
     */
    public function addLine(
        int   $itemId,
        float $actualQuantity,
        float $countedQuantity,
        ?string $remarks = null
    ): StockTakeLines
    {
        return DB::transaction(function () use ($itemId, $actualQuantity, $countedQuantity, $remarks) {
            $line = StockTakeLines::create([
                'StockTakeId' => $this->stockTake->Id,
                'ItemId' => $itemId,
                'ActualQuantity' => $actualQuantity,
                'CountedQuantity' => $countedQuantity,
                'Remarks' => $remarks,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($line)
                ->event('create')
                ->log("Added line item to Stock Take #{$this->stockTake->Id}");

            return $line;
        });
    }

    public function getStockTake(): StockTake
    {
        return $this->stockTake;
    }
}
