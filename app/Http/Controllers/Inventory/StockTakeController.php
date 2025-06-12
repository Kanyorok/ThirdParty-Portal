<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\StockTake;
use App\Models\Inventory\StockItem;


class StockTakeController extends Controller
{
    //
    public function index()
    {
        $stocks = StockTake::all();
        return view('inventory.stockmanagement.stocktake.index',compact ('stocks'));
    }

    public function create(){
        $items = StockItem::all();
        return view('inventory.stockmanagement.stocktake.create',compact('items'));
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
           return redirect()->route('stocktake.index')->with('success','property block created successfully');
    }
}
