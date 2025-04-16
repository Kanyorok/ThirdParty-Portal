<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\ItemCategory;

class ItemCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = ItemCategory::all();
        return view('procurement.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('procurement.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        ItemCategory::create([
            'Name'        => $request->name,
            'Description' => $request->description,
            'CreatedBy'   => auth()->user()->Id,
            'ModifiedBy'  => auth()->user()->Id,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $category = ItemCategory::findOrFail($id);
        return view('procurement.categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $category = ItemCategory::findOrFail($id);
        $category->update([
            'Name'        => $request->name,
            'Description' => $request->description,
            'ModifiedBy'  => auth()->user()->Id,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = ItemCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
