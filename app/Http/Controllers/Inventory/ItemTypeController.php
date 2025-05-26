<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ItemType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ItemTypeController extends Controller
{
    public function index()
    {
        $itemtypes = ItemType::all();
        return view('inventory.itemmaster.itemtype.index', compact('itemtypes'));
    }
        public function create()
    {
        return view('inventory.itemmaster.itemtype.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'TypeName' => 'required|string',
            'StockTracked' => 'required|boolean',
            'RequiresTagging' => 'required|boolean',
            'Active' => 'nullable|boolean',
        ]);

        $itemtype = ItemType::create([
            'TypeName' => $request->TypeName,
            'StockTracked' => $request->StockTracked,
            'RequiresTagging' => $request->RequiresTagging,
            'Active' => $request->Active,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => Carbon::now(),
        ]);

        return redirect()->route('itemtype.index')->with('success', 'Inventory type created successfully.');
    
    }

    public function show($Id)
    {
        $itemtype = ItemType::findOrFail($Id);
        return response()->json($itemtype);
    }

    public function edit($Id)
    {
        $itemtype = ItemType::findOrFail($Id);
        return response()->json($itemtype);
    }


    public function update(Request $request, $Id)
{
    $request->validate([
        'TypeName' => 'required|string',
        'StockTracked' => 'required|boolean',
        'RequiresTagging' => 'required|boolean',
        'Active' => 'nullable|boolean',
    ]);

    $itemtype = ItemType::findOrFail($Id);

    $itemtype->update([
        'TypeName' => $request->TypeName,
        'StockTracked' => $request->StockTracked,
        'RequiresTagging' => $request->RequiresTagging,
        'Active' => $request->input('Active', 0),
        'ModifiedBy' => Auth::id(),
        'ModifiedOn' => Carbon::now(),
    ]);

    return redirect()->route('itemtype.index')->with('success', 'Item Type Updated successfully.');
}


    public function destroy($Id)
    {
        $itemtype = ItemType::findOrFail($Id);
        $itemtype->DeletedBy = Auth::id();
        $itemtype->save();
        $itemtype->delete();

        return redirect()->route('itemtype.index')->with('success', 'Item Type Deleted successfully.');
    }
}