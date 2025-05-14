<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemSubCategories;
use App\Models\Inventory\ItemCategories;

class ItemSubCategoryController extends Controller
{
    public function index()
    {
        $items = ItemSubCategories::with('parentCategory')->get(); 
        return view('inventory.itemmaster.itemsubcategory.index', compact('items'));
    }

    public function create()
    {
        $categories = ItemCategories::all(); // list of all parent categories
        return view('inventory.itemmaster.itemsubcategory.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'SubCategoryCode' => 'required|string|max:50',
            'SubCategoryName' => 'required|string|max:255',
            'ParentCategory'  => 'required|exists:t_ItemCategories,id',
            'Description'     => 'nullable|string',
            'Status'          => 'nullable|boolean',
        ]);

        ItemSubCategories::create($validatedData);

        return redirect()->route('itemsubcategory.index')->with('success', '✅ Subcategory added successfully!');
    }

    public function show($Id)
    {
        $item = ItemSubCategories::with('parentCategory')->findOrFail($Id);
        return view('inventory.itemmaster.itemsubcategory.show', compact('item'));
    }

    public function edit($Id)
    {
        $item = ItemSubCategories::findOrFail($Id);
        $categories = ItemCategories::all(); // for dropdown
        return view('inventory.itemmaster.itemsubcategory.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $Id)
    {
        $item = ItemSubCategories::findOrFail($Id);

        $validatedData = $request->validate([
            'SubCategoryCode' => 'required|string|max:50',
            'SubCategoryName' => 'required|string|max:255',
            'ParentCategory'  => 'required|exists:t_ItemCategories,id',
            'Description'     => 'nullable|string',
            'Status'          => 'nullable|boolean',
        ]);

        $item->update($validatedData);

        return redirect()->route('itemsubcategory.index')->with('success', '✅ Subcategory updated successfully!');
    }


    public function destroy($Id)
    {
        $item = ItemSubCategories::findOrFail($Id);
        $item->delete();

        return redirect()->route('itemsubcategory.index')->with('success', '🗑️ Subcategory deleted successfully!');
    }
}
