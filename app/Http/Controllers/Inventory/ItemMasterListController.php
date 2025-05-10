<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemMasterList;

class ItemMasterListController extends Controller
{
    public function index()
    {
        $items = ItemMasterList::all(); // 
        return view('inventory.item master.item master list.index', compact('items'));
    }

    public function create()
    {
        return view('inventory.item master.item master list.create');
    }

    public function store(Request $request)
    {
        
        $validatedData = $request->validate([
            'ItemCode'      => 'required',
            'BarCode'       => 'required',
            'ItemName'      => 'required',
            'ItemType'      => 'required',
            'Category'      => 'required',
            'SubCategory'   => 'required',
            'UOM'           => 'required',
            'InventoryType' => 'required',
            'imageUpload'   => 'nullable|image|.pdf,.doc,.docx', 
        ]);
       
        if ($request->hasFile('imageUpload')) {
        $imagePath = $request->file('imageUpload')->store('items', 'public');
        $validatedData['imageUpload'] = $imagePath;
    }

        ItemMasterList::create($validatedData);

        return redirect()->route('itemmaster.index')->with('success', 'Item added successfully!');
    }
}

