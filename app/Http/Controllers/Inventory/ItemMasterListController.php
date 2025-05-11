<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemMasterList;

class ItemMasterListController extends Controller
{
   
    public function index()
    {
        $items = ItemMasterList::all();
        return view('inventory.item master.item master list.index', compact('items'));
    }

  
    public function create()
    {
        return view('inventory.item master.item master list.create');
    }

   
 public function store(Request $request)
{
    // Validate input fields
    $validatedData = $request->validate([
        'ItemCode'      => 'required|unique:t_ItemMasterList,ItemCode',
        'BarCode'       => 'required',
        'ItemName'      => 'required',
        'ItemType'      => 'required',
        'Category'      => 'required',
        'SubCategory'   => 'required',
        'UOM'           => 'required',
        'InventoryType' => 'required',
        'imageUpload'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ], [
        'ItemCode.unique' => '🚨 The ItemCode already exists! Please choose a different code.',
    ]);
   
    if ($request->hasFile('imageUpload')) {
        $imagePath = $request->file('imageUpload')->store('items', 'public');
        $validatedData['imageUpload'] = $imagePath;
    }

    ItemMasterList::create($validatedData);

    return redirect()->route('itemmaster.index')->with('success', '✅ Item added successfully!');
}


    public function show($ItemCode)
    {
        $item = ItemMasterList::findOrFail($ItemCode);
        return view('inventory.item master.item master list.show', compact('item'));
    }

    public function edit($ItemCode)
{
    $item = ItemMasterList::where('ItemCode', $ItemCode)->firstOrFail();
    return view('inventory.item master.item master list.edit', compact('item'));
}

public function update(Request $request, $ItemCode)
{
    $item = ItemMasterList::where('ItemCode', $ItemCode)->firstOrFail();

    $validatedData = $request->validate([
        'ItemCode'      => 'required',
        'BarCode'       => 'required',
        'ItemName'      => 'required',
        'ItemType'      => 'required',
        'Category'      => 'required',
        'SubCategory'   => 'required',
        'UOM'           => 'required',
        'InventoryType' => 'required',
        'imageUpload'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($request->hasFile('imageUpload')) {
        $imagePath = $request->file('imageUpload')->store('items', 'public');
        $validatedData['imageUpload'] = $imagePath;
    }

    $item->update($validatedData);

    return redirect()->route('itemmaster.index')->with('success', '✅ Changes saved successfully!');
}

  
    public function destroy($ItemCode)
    {
        $item = ItemMasterList::findOrFail($ItemCode);
        $item->delete();

        return redirect()->route('itemmaster.index')->with('success', 'Item deleted successfully!');
    }
}
