<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ItemCategoryController extends Controller
{
    public function index()
    {
        $categories = ItemCategories::whereNull('ParentId')->with('parent')->get();
        return view('inventory.itemmaster.itemcategory.index', compact('categories'));
    }

    public function create()
    {
        $categories = ItemCategories::whereNull('ParentId')->get();
        return view('inventory.itemmaster.itemcategory.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'Name' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'ParentId' => 'nullable|exists:t_ItemCategories,Id',
        ]);

        $validatedData['CreatedBy'] = Auth::id();
        $validatedData['ModifiedBy'] = Auth::id();
        $validatedData['CreatedOn'] = Carbon::now();
        $validatedData['ModifiedOn'] = Carbon::now();

        ItemCategories::create($validatedData);

        return redirect()->route('itemcategory.index')->with('success', 'Category created successfully.');
    }

    public function show($Id)
    {
        $category = ItemCategories::with('parent', 'children')->findOrFail($Id);
        return view('inventory.itemmaster.itemcategory.show', compact('category'));
    }

    public function edit($Id)
    {
        $category = ItemCategories::with('parent')->findOrFail($Id);
        $categories = ItemCategories::whereNull('ParentId')->where('Id', '!=', $Id)->get(); // Avoid self-parenting

        return view('inventory.itemmaster.itemcategory.edit', compact('category', 'categories'));
    }

    public function update(Request $request, $Id)
    {
        $validatedData = $request->validate([
            'Name' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'ParentId' => 'nullable|exists:t_ItemCategories,Id',
        ]);

        $category = ItemCategories::findOrFail($Id);
        $validatedData['ModifiedBy'] = Auth::id();
        $validatedData['ModifiedOn'] = Carbon::now();

        $category->update($validatedData);

        return redirect()->route('itemcategory.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($Id)
    {
        $category = ItemCategories::findOrFail($Id);
        $category->DeletedBy = Auth::id();
        $category->DeletedOn = Carbon::now();
        $category->save();
        $category->delete();

        return redirect()->route('itemcategory.index')->with('success', 'Category updated successfully.');
    }
}
