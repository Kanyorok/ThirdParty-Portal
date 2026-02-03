<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $currentBranch = Auth::user()->branch;
        if (! $currentBranch instanceof Branch) {
            return redirect()->back()->with('fail', 'Current user branch not found.');
        }

        $branchId = $currentBranch->Id;
        $isHeadOffice = $branchId === 1;

        $branches = $isHeadOffice ? Branch::all() : Branch::where('Id', $branchId)->get();

        $stores = $isHeadOffice
            ? Store::all()
            : Store::where('BranchID', $branchId)->get();

        $items = ItemMasterList::all();

        $baseQuery = StockTransaction::whereNull('t_StockTransactions.DeletedOn');

        if (! $isHeadOffice) {
            $baseQuery->where('BranchID', $branchId);
        }

        if ($request->branch && $isHeadOffice) {
            $baseQuery->where('BranchID', $request->branch);
        }

        if ($request->store) {
            $baseQuery->where('StoreID', $request->store);
        }

        if ($request->item) {
            $baseQuery->where('ItemID', $request->item);
        }

        if ($request->from_date) {
            $baseQuery->whereDate('TransactionDate', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $baseQuery->whereDate('TransactionDate', '<=', $request->to_date);
        }

        $transactions = $baseQuery->clone()
            ->select([
                'ItemID',
                DB::raw('SUM(QuantityIn) as total_in_qty'),
                DB::raw('SUM(QuantityOut) as total_out_qty'),
                DB::raw('MAX(BalanceQty) as closing_qty'),
                DB::raw('SUM(CASE WHEN QuantityIn > 0 THEN TotalCost ELSE 0 END) as total_in_value'), // Only sum positive costs for IN
                DB::raw('SUM(CASE WHEN QuantityOut > 0 THEN TotalCost ELSE 0 END) as total_out_value'), // Only sum positive costs for OUT
                DB::raw('AVG(UnitCost) as avg_unit_cost'), // Get average unit cost from transactions
            ])
            ->groupBy('ItemID')
            ->get();

        $movementData = [];
        $totalOpening = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalClosing = 0;
        $totalValueOpening = 0;
        $totalValueIn = 0;
        $totalValueOut = 0;
        $totalValueClosing = 0;

        foreach ($transactions as $tx) {
            $openingQty = $tx->closing_qty - ($tx->total_in_qty - $tx->total_out_qty);
            $closingQty = $tx->closing_qty;

            $item = ItemMasterList::find($tx->ItemID);

            $unitCost = $this->getUnitPriceForItem($tx, $item);

            $openingValue = $openingQty * $unitCost;
            $inValue = $tx->total_in_value > 0 ? $tx->total_in_value : ($tx->total_in_qty * $unitCost);
            $outValue = $tx->total_out_value > 0 ? $tx->total_out_value : ($tx->total_out_qty * $unitCost);
            $closingValue = $closingQty * $unitCost;

            if (count($movementData) === 0) {
                \Log::debug('Transaction calculation:', [
                    'item_id' => $tx->ItemID,
                    'opening_qty' => $openingQty,
                    'in_qty' => $tx->total_in_qty,
                    'out_qty' => $tx->total_out_qty,
                    'closing_qty' => $closingQty,
                    'unit_cost' => $unitCost,
                    'opening_value' => $openingValue,
                    'in_value' => $inValue,
                    'out_value' => $outValue,
                    'closing_value' => $closingValue,
                    'tx_avg_unit_cost' => $tx->avg_unit_cost,
                    'tx_total_in_value' => $tx->total_in_value,
                    'tx_total_out_value' => $tx->total_out_value,
                    'item_unit_cost' => $item ? $item->UnitCost : 'no item',
                ]);
            }

            $movementData[$tx->ItemID] = [
                'label' => $item ? ($item->ItemName ?? $item->Description) : 'Unknown',
                'quantity' => [
                    (float) $openingQty,
                    (float) $tx->total_in_qty,
                    (float) $tx->total_out_qty,
                    (float) $closingQty,
                ],
                'value' => [
                    (float) $openingValue,
                    (float) $inValue,
                    (float) $outValue,
                    (float) $closingValue,
                ],
            ];

            $totalOpening += $openingQty;
            $totalIn += $tx->total_in_qty;
            $totalOut += $tx->total_out_qty;
            $totalClosing += $closingQty;

            $totalValueOpening += $openingValue;
            $totalValueIn += $inValue;
            $totalValueOut += $outValue;
            $totalValueClosing += $closingValue;
        }

        $dailyMovement = $this->getDailyMovementData($baseQuery);
        $branchMovement = $this->getBranchMovementData($baseQuery, $isHeadOffice);
        $topItems = $this->getTopMovingItems($baseQuery);

        $netMovement = $totalIn - $totalOut;

        return view('inventory.inventorydashboard.stockmovement.index', compact(
            'branches',
            'stores',
            'items',
            'movementData',
            'dailyMovement',
            'branchMovement',
            'topItems',
            'isHeadOffice',
            'currentBranch',
            'totalIn',
            'totalOut',
            'netMovement',
            'totalOpening',
            'totalClosing',
            'totalValueOpening',
            'totalValueClosing',
            'totalValueIn',
            'totalValueOut' // Added these
        ));
    }

    private function getUnitPriceForItem($transaction, $item = null)
    {
        if (isset($transaction->avg_unit_cost) && $transaction->avg_unit_cost > 0) {
            return $transaction->avg_unit_cost;
        }

        $totalQty = $transaction->total_in_qty + $transaction->total_out_qty;
        $totalValue = $transaction->total_in_value + $transaction->total_out_value;

        if ($totalQty > 0 && $totalValue > 0) {
            return $totalValue / $totalQty;
        }

        if ($item && isset($item->UnitCost) && $item->UnitCost > 0) {
            return $item->UnitCost;
        }

        return 100;
    }

    private function getDailyMovementData($baseQuery)
    {
        $dailyAggregates = $baseQuery->clone()
            ->select([
                DB::raw('CAST(TransactionDate AS date) as date'),
                DB::raw('SUM(QuantityIn) as in_qty'),
                DB::raw('SUM(QuantityOut) as out_qty'),
                DB::raw('SUM(CASE WHEN QuantityIn > 0 THEN TotalCost ELSE 0 END) as in_value'),
                DB::raw('SUM(CASE WHEN QuantityOut > 0 THEN TotalCost ELSE 0 END) as out_value'),
                DB::raw('AVG(UnitCost) as avg_unit_cost'),
            ])
            ->groupBy(DB::raw('CAST(TransactionDate AS date)'))
            ->orderBy('date')
            ->get();

        $dailyData = [];
        $previousClosingQty = 0;
        $previousClosingValue = 0;

        foreach ($dailyAggregates as $index => $day) {
            $unitCost = $day->avg_unit_cost > 0 ? $day->avg_unit_cost : 100;

            $openingQty = $index === 0 ? 0 : $previousClosingQty;
            $closingQty = $openingQty + $day->in_qty - $day->out_qty;

            $openingValue = $openingQty * $unitCost;

            $inValue = $day->in_value > 0 ? $day->in_value : ($day->in_qty * $unitCost);
            $outValue = $day->out_value > 0 ? $day->out_value : ($day->out_qty * $unitCost);
            $closingValue = $closingQty * $unitCost;

            $dailyData[$day->date] = [
                'date' => $day->date,
                'in_qty' => (float) $day->in_qty,
                'out_qty' => (float) $day->out_qty,
                'in_value' => (float) $inValue,
                'out_value' => (float) $outValue,
                'opening_qty' => (float) $openingQty,
                'closing_qty' => (float) $closingQty,
                'opening_value' => (float) $openingValue,
                'closing_value' => (float) $closingValue,
            ];

            $previousClosingQty = $closingQty;
            $previousClosingValue = $closingValue;
        }

        return collect($dailyData)->values();
    }

    private function getBranchMovementData($baseQuery, $isHeadOffice)
    {
        if (! $isHeadOffice) {
            return collect();
        }

        return $baseQuery->clone()
            ->join('t_Branches', 't_StockTransactions.BranchID', '=', 't_Branches.Id')
            ->whereNull('t_Branches.DeletedOn')
            ->select([
                't_Branches.Name as branch_name',
                DB::raw('SUM(QuantityIn - QuantityOut) as net_movement'),
                DB::raw('SUM(QuantityIn) as total_in_qty'),
                DB::raw('SUM(QuantityOut) as total_out_qty'),
                DB::raw('SUM(CASE WHEN QuantityIn > 0 THEN TotalCost ELSE 0 END) as total_in_value'),
                DB::raw('SUM(CASE WHEN QuantityOut > 0 THEN TotalCost ELSE 0 END) as total_out_value'),
                DB::raw('AVG(UnitCost) as avg_unit_cost'),
            ])
            ->groupBy('t_Branches.Id', 't_Branches.Name')
            ->get()
            ->map(function ($item) {
                $unitCost = $item->avg_unit_cost > 0 ? $item->avg_unit_cost : 100;

                return [
                    'branch_name' => $item->branch_name,
                    'net_movement' => (float) $item->net_movement,
                    'total_in' => (float) $item->total_in_qty,
                    'total_out' => (float) $item->total_out_qty,
                    'total_value_in' => (float) ($item->total_in_value > 0 ? $item->total_in_value : ($item->total_in_qty * $unitCost)),
                    'total_value_out' => (float) ($item->total_out_value > 0 ? $item->total_out_value : ($item->total_out_qty * $unitCost)),
                    'total_value' => (float) ($item->total_in_value + $item->total_out_value),
                ];
            });
    }

    private function getTopMovingItems($baseQuery, $limit = 10)
    {
        return $baseQuery->clone()
            ->join('t_Items', 't_StockTransactions.ItemID', '=', 't_Items.Id')
            ->whereNull('t_Items.DeletedOn')
            ->select([
                't_Items.ItemName as item_name',
                't_StockTransactions.ItemID',
                DB::raw('SUM(QuantityIn + QuantityOut) as total_movement'),
                DB::raw('SUM(QuantityIn) as total_in_qty'),
                DB::raw('SUM(QuantityOut) as total_out_qty'),
                DB::raw('SUM(CASE WHEN QuantityIn > 0 THEN TotalCost ELSE 0 END) as total_in_value'),
                DB::raw('SUM(CASE WHEN QuantityOut > 0 THEN TotalCost ELSE 0 END) as total_out_value'),
                DB::raw('AVG(UnitCost) as avg_unit_cost'),
            ])
            ->groupBy('t_StockTransactions.ItemID', 't_Items.ItemName')
            ->orderByDesc('total_movement')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $unitCost = $item->avg_unit_cost > 0 ? $item->avg_unit_cost : 100;

                return [
                    'item_name' => $item->item_name,
                    'total_movement' => (float) $item->total_movement,
                    'total_in' => (float) $item->total_in_qty,
                    'total_out' => (float) $item->total_out_qty,
                    'total_value_in' => (float) ($item->total_in_value > 0 ? $item->total_in_value : ($item->total_in_qty * $unitCost)),
                    'total_value_out' => (float) ($item->total_out_value > 0 ? $item->total_out_value : ($item->total_out_qty * $unitCost)),
                    'total_value' => (float) ($item->total_in_value + $item->total_out_value),
                ];
            });
    }
}
