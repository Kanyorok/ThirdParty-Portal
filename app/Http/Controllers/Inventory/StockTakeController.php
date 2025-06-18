<?php

namespace App\Http\Controllers\Inventory;

use App\Models\Inventory\StockTakeLines;
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

    public function create(){
        $stocks = StockItem::all();
        $branches = Branch::all();
        $stores =   Store::all();
        return view('inventory.stockmanagement.stocktake.create',compact('stocks', 'branches','stores'));
    }

    public function getStoreByBranch($storeId)
    {   
        $stores = Store::where('BranchID',$storeId)->get();
        return response()->json($stores);
    }

    public function show($id){
        $stock = StockTake::find($id);
        return view('inventory.stockmanagement.stocktake.show',compact('stock'));
    }
    
     public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'BranchId'=>'required|string|max:50',
            'StoreId'=>'required|string|max:50',
            'CountedBy'=>'required|string|max:100',
            'CountDate'=>'required|date|max:100',
            
        ]);
         $stocks = StockTake::create([
            'BranchId'=> $request->BranchId,
            'StoreId'=> $request->StoreId,
            'CountedBy'=> $request->CountedBy   ,
            'CountDate' => $request->CountDate,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
           
    }
    public function getStoreItems($storeId)
    {
    $items = \App\Models\Inventory\StoreItem::where('StoreId', $storeId)->get([
        'Id', 'ItemName', 'Quantity' // Adjust to your actual column names
    ]);

    return response()->json($items);
    }

    public function storeline(Request $request)
    {
          $request->validate([
            'StockTakeId'=>'required|string|max:50',
            'ItemId'=>'required|string|max:50',
            'ActualQuantity'=>'required|integer|max:50',
            'CountedQuantity'=>'required|integer|max:50',
            'Remarks'=>'required|string|max:100'
          ]);
            $stocklines = StockTakeLines::create([
            'StockTakeId'=> $request->StockTakeId,
            'ItemId'=> $request->ItemId,
            'ActualQuantity'=> $request->ActualQuantity,
            'CountedQuantity' => $request->CounedQuantity,
            'Remarks' => $request->Remarks,   
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        return redirect()->route('stocktake.index')->with('success','Stock Take AND Stock Take Lines created successfully');
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

