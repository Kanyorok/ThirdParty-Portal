<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemCategories;

class ItemCategoryController extends Controller
{
   
    public function index()
    {
        $items = ItemCategories::all();
        return view('inventory.itemmaster.itemcategory.index', compact('items'));
    }

  
    public function create()
    {
        return view('inventory.itemmaster.itemcategory.create');
    }

   
 public function store(Request $request)
{
    // Validate input fields
    $validatedData = $request->validate([
        'CategoryCode' => 'required|string|max:50',
        'Name'         => 'required|string|max:255',
        'Description'  => 'nullable|string|max:500',
        'Status'       => 'nullable|boolean',
        'CreatedBy'    => 'exists:t_Users,Id',
        'ModifiedBy'   => 'exists:t_Users,Id',
        'DeletedBy'    => 'exists:t_Users,Id',
]);


    ItemCategories::create($validatedData);

    return redirect()->route('itemcategory.index')->with('success', '✅ Item added successfully!');
}


    public function show($id)
    {
        $item = ItemCategories::findOrFail($id);
        return view('inventory.itemmaster.itemcategory.show', compact('item'));
    }

    public function edit($id)
{
    $item = ItemCategories::where('id', $id)->firstOrFail();
    return view('inventory.itemmaster.itemcategory.edit', compact('item'));
}

public function update(Request $request, $id)
{
    $item = ItemCategories::where('id', $id)->firstOrFail();

    $validatedData = $request->validate([
            'CategoryCode' => 'required|string|max:50',
            'Name'         => 'required|string|max:255',
            'Description'  => 'nullable|string|max:500',
            'Status'       => 'required|boolean',
            'CreatedBy'    => 'exists:t_Users,Id',
            'ModifiedBy'   => 'exists:t_Users,Id',
            'DeletedBy'    => 'exists:t_Users,Id',

    ]);

    $item->update($validatedData);

    return redirect()->route('itemcategory.index')->with('success', '✅ Changes saved successfully!');
}

  
    public function destroy($id)
    {
        $item = ItemCategories::findOrFail($id);
        $item->delete();

        return redirect()->route('itemcategory.index')->with('success', 'Item deleted successfully!');
    }
}
