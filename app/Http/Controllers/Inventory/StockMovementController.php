<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\ItemMasterList;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::all();
        $stores = Store::all();
        $items = ItemMasterList::all();

        $query = StockTransaction::query();

        if ($request->branch) {
            $query->where('BranchID', $request->branch);
        }

        if ($request->store) {
            $query->where('StoreID', $request->store);
        }

        if ($request->item) {
            $query->where('ItemID', $request->item);
        }

        if ($request->from_date) {
            $query->whereDate('TransactionDate', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('TransactionDate', '<=', $request->to_date);
        }

        // Group by item
        $transactions = $query->select(
            'ItemID',
            DB::raw('SUM(QuantityIn) as total_in'),
            DB::raw('SUM(QuantityOut) as total_out'),
            DB::raw('MAX(BalanceQty) as closing_qty'),
            DB::raw('SUM(TotalCost) as total_value')
        )
        ->groupBy('ItemID')
        ->get();

        // Calculate opening balance
        $movementData = [];
        foreach ($transactions as $tx) {
            $opening = $tx->closing_qty - ($tx->total_in - $tx->total_out);
            $movementData[$tx->ItemID] = [
                'label' => $tx->item->Description ?? 'Unknown',
                'quantity' => [
                    $opening,
                    $tx->total_in,
                    $tx->total_out,
                    $tx->closing_qty
                ],
                'value' => [
                    $opening * ($tx->item->UnitCost ?? 0),
                    $tx->total_in * ($tx->item->UnitCost ?? 0),
                    $tx->total_out * ($tx->item->UnitCost ?? 0),
                    $tx->total_value
                ]
            ];
        }

        return view('inventory.inventorydashboard.stockmovement.index', compact(
            'branches', 'stores', 'items', 'movementData'
        ));
    }
}

