<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Requests\Inventory\StockTakeRequest;
use App\Models\Inventory\StockTakeLines;
use App\Services\Inventory\StockTakeService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\StockTake;
use App\Models\Inventory\StockItem;
use App\Models\Core\Branch;
use App\Models\Inventory\Store;


class StockTakeController extends Controller
{
    //
    public function index()
    {
        $stocks = StockTake::all();
        return view('inventory.stockmanagement.stocktake.index',compact ('stocks'));
    }

    public function create()
    {
        $branches = Branch::all(); // Replace with your actual Branch model
        $stocks = collect(); // Empty collection to avoid undefined error
        return view('inventory.stockmanagement.stocktake.create', compact('branches', 'stocks'));
    }


    public function getStoreByBranch($storeId)
    {   
        $stores = Store::where('BranchID',$storeId)->get();
        return response()->json($stores);
    }

    public function getStockItems($branchId, $storeId)
    {
        $stocks = StockItem::where('Branch', $branchId)
                    ->where('Store', $storeId)
                    ->whereNull('DeletedOn')
                    ->with('item')
                    ->get();

        return response()->json($stocks); // Just return raw data
    }
    
public function store(StockTakeRequest $request)
{
    try {
        $validated = $request->validated();

        // Fetch the Branch and Store models using the validated IDs
        $branch = Branch::findOrFail($validated['BranchId']);
        $store = Store::findOrFail($validated['StoreId']);

        $service = StockTakeService::create(
            branch: $branch,
            store: $store,
            countedBy: $validated['CountedBy'],
            countDate: Carbon::parse($validated['CountDate']),
        );

        foreach ($validated['lines'] as $line) {
            $service->addLine(
                itemId: $line['ItemId'],
                actualQuantity: $line['ActualQuantity'],
                countedQuantity: $line['CountedQuantity'],
                remarks: $line['Remarks'] ?? null
            );
        }

        return redirect()->route('stocktake.index')->with('success', 'Stock Take and Lines created successfully.');
    } catch (\Exception $e) {
        \Log::error('StockTake store error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to create Stock Take: ' . $e->getMessage())->withInput();
    }
}

    public function show($id){
        $stock = StockTake::find($id);
        return view('inventory.stockmanagement.stocktake.show',compact('stock'));
    }

    public function edit($id)
    {
        //Check if user has permission to edit tender categories
       // $this->authorize(PermissionEnum::PropertyTypeUpdate, PropertyType::class);
        $stock = StockTake::findOrFail($id);
        $items = StockItem::all();

        return view('inventory.stockmanagement.stocktake.edit',compact('stock','items'));
    }
     public function update(Request $request, $id){ 
       // $this->authorize(PermissionEnum::PropertyTypeUpdate , PropertyType::class);
        $validated=$request->validate([
        'BranchId'  => 'required|exists:t_StockItems,Id',
        'StoreId' => 'required|exists:t_StockItems,Id',
        'CountedBy'  => 'required|string|max:100',
        'CountDate'  => 'required|string|max:100',
        
        
    ]);
 
    DB::beginTransaction();
 
    try{
        $stock = StockTake::findOrFail($id);

        $stock->update([
            'BranchId'  => $validated['BranchId'],
            'StoreId' => $validated['StoreId'],
            'CountedBy'  => $validated['CountedBy'],  
            'CountDate'  => $validated['CountDate'],       

            'ModifiedBy' => Auth::Id(),
        ]);
 
        DB::commit();
        activity()
                ->performedOn($stock)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Stock Take');

                return redirect()->route('stocktake.index')->with('success' , 'StockTake updated successfully');
            }catch(\Throwable $th) {
                DB::rollBack();
                Log::error('Failed to Update StockTake:' . $th->getMessage());

                return back()->withErrors(['error'=>'Failed to update StockTake'])->withInput();
            }
       }
       public function destroy($id)
    {
        //Check if user has permission to delete property categories
        //$this->authorize(PermissionEnum::PropertyTypeDelete , PropertyType::class);
        try {
            $stock = StockTake::findOrFail($id);
            $stock->delete();

            return redirect()->route('stocktake.index')
                ->with('success', 'Stock Take Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Stock Take: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Stock Take. Please try again.'])
                ->withInput();
        }
    }   

}

