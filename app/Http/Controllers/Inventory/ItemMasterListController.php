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
        return view('inventory.itemmaster.itemmasterlist.index', compact('items'));
    }

  
    public function create()
    {
        return view('inventory.itemmaster.itemmasterlist.create');
    }

   
 public function store(Request $request)
{
    // Validate input fields
    $validatedData = $request->validate([
        'ItemCode'      => 'required',
        'BarCode'       => 'required',
        'ItemName'      => 'required',
        'ItemType'      => 'required',
        'Category'      => 'required',
        'SubCategory'   => 'required',
        'UOM'           => 'required',
        'InventoryType' => 'required',
        'ImageUpload'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'ItemDescription' => 'nullable',
        'DocumentUpload' => 'nullable',
    ], [
        'ItemCode.unique' => '🚨 The ItemCode already exists! Please choose a different code.',
    ]);
   
    if ($request->hasFile('ImageUpload')) {
        $imagePath = $request->file('ImageUpload')->store('items', 'public');
        $validatedData['ImageUpload'] = $imagePath;
    }

    ItemMasterList::create($validatedData);

    return redirect()->route('itemmaster.index')->with('success', '✅ Item added successfully!');
}


    public function show($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        return view('inventory.itemmaster.itemmasterlist.show', compact('item'));
    }

    public function edit($Id)
{
    $item = ItemMasterList::where('Id', $Id)->firstOrFail();
    return view('inventory.itemmaster.itemmasterlist.edit', compact('item'));
}

public function update(Request $request, $Id)
{
    $item = ItemMasterList::where('Id', $Id)->firstOrFail();

    $validatedData = $request->validate([
        'ItemCode'      => 'required',
        'BarCode'       => 'required',
        'ItemName'      => 'required',
        'ItemType'      => 'required',
        'Category'      => 'required',
        'SubCategory'   => 'required',
        'UOM'           => 'required',
        'InventoryType' => 'required',
        'ImageUpload'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'ItemDescription' => 'nullable',
        'DocumentUpload' => 'nullable'
    ]);
  if ($request->hasFile('ImageUpload')) {
        $path = $request->file('ImageUpload')->store('items', 'public');
        $item->ImageUpload = $path;
    }

    $item->update($validatedData);

    return redirect()->route('itemmaster.index')->with('success', '✅ Changes saved successfully!');
}

  
    public function destroy($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $item->delete();

        return redirect()->route('itemmaster.index')->with('success', 'Item deleted successfully!');
    }
}
