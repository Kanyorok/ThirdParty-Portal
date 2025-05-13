<?php
namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemSubCategory;
use App\Models\Inventory\ItemCategories;

class ItemSubCategoryController extends Controller
{

    public function index()
    {
        $items = ItemSubCategory::with('ParentCategory')->get();
        return view('itemsubcategory.index', compact('items'));
    }

    public function create()
    {
        $items = ItemCategories::all(); // Fetch all categories
        return view('itemsubcategory.create', compact('items'));
    }

    public function store(Request $Id)
    {
        $request->validate([
            'SubCategoryName' => 'required|string|max:255',
            'SubCategoryCode' => 'required|string|max:50',
            'ParentCategory'  => 'required|exists:t_ItemCategories,id',
            'Description'     => 'nullable|string',
            'Status'          => 'nullable|boolean',
        ]);

        ItemSubCategory::create($Id->all());

        return redirect()->route('itemsubcategory.index')->with('success', 'Subcategory created successfully.');
    }

    public function show(ItemSubCategory $item)
    {
        return view('itemsubcategory.show', compact('items'));
    }

    public function edit(ItemSubCategory $item)
    {
        $items = ItemCategories::all();
        return view('itemsubcategory.edit', compact('items', 'categories'));
    }

    // Update an existing subcategory
    public function update(Request $r, ItemSubCategory $subCategory)
    {
        $request->validate([
            'SubCategoryName' => 'required|string|max:255',
            'SubCategoryCode' => 'required|string|max:50',
            'ParentCategory'  => 'required|exists:t_ItemCategories,id',
            'Description'     => 'nullable|string',
            'Status'          => 'nullable|boolean',
        ]);

        $items->update($request->all());

        return redirect()->route('itemsubcategory.index')->with('success', 'Subcategory updated successfully.');
    }

    // Delete a subcategory
    public function destroy(ItemSubCategory $Id)
    {
        $item->delete();
        return redirect()->route('itemsubcategory.index')->with('success', 'Subcategory deleted successfully.');
    }
}

