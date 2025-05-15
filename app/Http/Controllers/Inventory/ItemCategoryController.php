<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemCategories;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ItemCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Datatables::of(ItemCategories::whereNull('ParentId')->with('parent')->select('t_ItemCategory.*'))
                ->addColumn('Action', function (ItemCategories $item) {
                    return '
                        <a href="' . route('itemcategory.show', ['id' => $item->Id]) . '" class="btn btn-sm btn-primary">View</a>
                        <a href="' . route('itemcategory.edit', ['id' => $item->Id]) . '" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" onclick="confirmDelete(' . $item->Id . ')" class="btn btn-sm btn-danger">Delete</a>';
                })
                ->rawColumns(['Action'])
                ->make(true);
        }

        return view('inventory.itemmaster.itemcategory.index');
    }

    public function create()
    {
        $categories = ItemCategories::whereNull('ParentId')->get(); // Only top-level
        return view('inventory.itemmaster.itemcategory.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'CategoryName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'ParentId' => 'nullable|exists:t_ItemCategory,Id',
        ]);

        $item = new ItemCategories();
        $item->fill($request->all());
        $item->CreatedBy = Auth::id();
        $item->CreatedOn = Carbon::now();
        $item->save();

        return redirect()->route('itemcategory.index')->with('success', 'Category created successfully.');
    }

    public function edit($Id)
    {
        $item = ItemCategories::findOrFail($Id);
        $categories = ItemCategories::whereNull('ParentId')->where('Id', '!=', $Id)->get(); // Avoid self-parenting
        return view('inventory.itemmaster.itemcategory.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $Id)
    {
        $request->validate([
            'CategoryName' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'ParentId' => 'nullable|exists:t_ItemCategory,Id',
        ]);

        $item = ItemCategories::findOrFail($Id);
        $item->fill($request->all());
        $item->ModifiedBy = Auth::id();
        $item->ModifiedOn = Carbon::now();
        $item->save();

        return redirect()->route('itemcategory.index')->with('success', 'Category updated successfully.');
    }

    public function show($Id)
    {
        $item = ItemCategories::with('parent', 'children')->findOrFail($Id);
        return view('inventory.itemmaster.itemcategory.show', compact('item'));
    }

    public function destroy($Id)
    {
        $item = ItemCategories::findOrFail($Id);
        $item->DeletedBy = Auth::id();
        $item->DeletedOn = Carbon::now();
        $item->save();
        $item->delete();

        return response()->json(['success' => 'Category deleted successfully.']);
    }
}
