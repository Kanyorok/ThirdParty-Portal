<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Requests\Inventory\StockTakeRequest;
use App\Models\Auth\User;
use App\Models\Inventory\StockTakeLines;
use App\Services\Inventory\StockTakeService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\StockTake;
use App\Providers\Inventory\StockTakePolicy;
use App\Models\Inventory\StockItem;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;
use App\Enums\Core\PermissionEnum;


class StockTakeController extends Controller
{
    
    public function index()
    {
        $this->authorize(PermissionEnum::StockTakeView, StockTake::class);
        $stocks = StockTake::with('branch', 'store', 'createdby', 'countedby')->get();
        return view('inventory.stockmanagement.stocktake.index', compact('stocks'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::StockTakeCreate, StockTake::class);
        $branches = Branch::all();
        $users = User::all();
        $stocks = collect();
        return view('inventory.stockmanagement.stocktake.create', compact('branches', 'stocks', 'users'));
    }

    public function getStoreByBranch($storeId)
    {
        $stores = Store::where('BranchID', $storeId)->get();
        return response()->json($stores);
    }

    public function getStockItems($branchId, $storeId)
    {
        $stocks = StockItem::where('Branch', $branchId)
            ->where('Store', $storeId)
            ->whereNull('DeletedOn')
            ->with('item')
            ->get();

        return response()->json($stocks); 
    }

    public function store(StockTakeRequest $request)
    {
        $this->authorize(PermissionEnum::StockTakeCreate, StockTake::class);
        $branch = Branch::findOrFail($request->BranchId);
        $store = Store::findOrFail($request->StoreId);
        $countedBy = $request->CountedBy;
        $countDate = Carbon::parse($request->CountDate);
        $lines = $request->lines;

        $stockTake = StockTakeService::createWithLines(
            branch: $branch,
            store: $store,
            countedBy: $countedBy,
            countDate: $countDate,
            lines: $lines
        );

        return redirect()
            ->route('stocktake.index')
            ->with('success', 'Stock Take recorded successfully.');
    }


    public function show($id)
    {
         $this->authorize(PermissionEnum::StockTakeView, StockTake::class);
        $stock = StockTake::with(['branch', 'store', 'lines.item.item'])->findOrFail($id);
        return view('inventory.stockmanagement.stocktake.show', compact('stock'));
    }


    public function edit($id)
    {
        $this->authorize(PermissionEnum::StockTakeUpdate, StockTake::class);
        $stock = StockTake::with('branch', 'store')->findOrFail($id);
        $branches = Branch::all();
        $stores = Store::all();
        $users = User::all();

        return view('inventory.stockmanagement.stocktake.edit', compact('stock', 'branches', 'stores', 'users'));
    }

public function update(Request $request, $id)
{
    $this->authorize('update', StockTake::class);
    
    $validated = $request->validate([
        'BranchId' => 'required|exists:t_Branches,Id',
        'StoreId' => 'required|exists:t_Stores,Id',
        'CountedBy' => 'required|exists:t_Users,Id',
        'CountDate' => 'required|date',
        'lines' => 'sometimes|array',
        'lines.*.Id' => 'sometimes|required|exists:t_StockTakeLines,Id',
        'lines.*.CountedQuantity' => ['required', 'numeric', 'min:0'],
        'lines.*.Remarks' => 'nullable|string',
    ], [
        'lines.*.CountedQuantity.min' => 'Counted quantity cannot be less than zero.',
    ]);

    DB::beginTransaction();

    try {
        $stock = StockTake::findOrFail($id);

        $stock->update([
            'BranchId' => $validated['BranchId'],
            'StoreId' => $validated['StoreId'],
            'CountedBy' => $validated['CountedBy'],
            'CountDate' => $validated['CountDate'],
            'ModifiedBy' => Auth::id(),
        ]);

        if (isset($validated['lines'])) {
            foreach ($validated['lines'] as $lineData) {
                if (!empty($lineData['Id'])) {
                    $line = StockTakeLines::find($lineData['Id']);

                    if ($line) {
                        $line->update([
                            'CountedQuantity' => $lineData['CountedQuantity'],
                            'Remarks' => $lineData['Remarks'] ?? null,
                            'ModifiedBy' => Auth::id(),
                            'ModifiedOn' => now(),
                        ]);
                    }
                }
            }
        }

        DB::commit();

        activity()
            ->performedOn($stock)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'update'])
            ->log('Updated Stock Take and lines');

        return redirect()->route('stocktake.index')->with('success', 'Stock Take updated successfully');
    } catch (\Throwable $th) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Failed to update Stock Take'])->withInput();
    }
}

    public function destroy($id)
    {
         $this->authorize(PermissionEnum::StockTakeDestroy, StockTake::class);

        try {
            $stock = StockTake::findOrFail($id);
            $stock->delete();

            return redirect()->route('stocktake.index')
                ->with('success', 'Stock Take Deleted Successfully!');
        } catch (\Throwable $th) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Stock Take. Please try again.'])
                ->withInput();
        }
    }

}
