<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class ItemMasterListController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return Datatables::of(ItemMasterList::with('category', 'subcategory')->select('t_ItemMasterList.*'))->addIndexColumn()
                ->addColumn('Action', function (ItemMasterList $item) {
                    return '
                        <a href="' . route('itemmasterlist.show', ['id' => $item->Id]) . '" class="btn btn-sm btn-primary">View</a>
                        <a href="' . route('itemmasterlist.edit', ['id' => $item->Id]) . '" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" onclick="confirmDelete(' . $item->Id . ')" class="btn btn-sm btn-danger">Delete</a>';
                })
                ->rawColumns(['Action'])
                ->make(true);
        }

        return view('inventory.itemmaster.itemmasterlist.index');
    }

    public function create()
    {
        $categories = ItemCategories::whereNull('ParentId')->get(); // Fetch only top-level categories
        return view('inventory.itemmaster.itemmasterlist.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ItemCode'      => 'required|string|max:255',
            'BarCode'       => 'required|string|max:255',
            'ItemName'      => 'required|string|max:255',
            'ItemType'      => 'required|string|max:255',
            'Category'      => 'required|exists:t_ItemCategory,Id',
            'SubCategory'   => 'nullable|exists:t_ItemCategory,Id', // Stores fetched subcategory
            'UOM'           => 'required|string|max:255',
            'InventoryType' => 'required|string|max:255',
            'ImageUpload'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'ItemDescription' => 'nullable|string',
            'DocumentUpload' => 'nullable'
        ]);

        DB::transaction(function () use ($request) {
            $item = ItemMasterList::create([
                'ItemCode' => $request->ItemCode,
                'BarCode' => $request->BarCode,
                'ItemName' => $request->ItemName,
                'ItemType' => $request->ItemType,
                'Category' => $request->Category,
                'SubCategory' => $request->SubCategory, // Stores subcategory in item master list
                'UOM' => $request->UOM,
                'InventoryType' => $request->InventoryType,
                'ItemDescription' => $request->ItemDescription,
                'CreatedBy' => Auth::id(),
                'UpdatedBy' => Auth::id(),
            ]);

            if ($request->hasFile('ImageUpload')) {
                $path = $request->file('ImageUpload')->store('items', 'public');
                $item->ImageUpload = $path;
                $item->save();
            }
        });

        return redirect()->route('itemmaster.index')->with('success', 'Item Master List created successfully!');
    }

    public function show($Id)
    {
        $item = ItemMasterList::with('category.parent')->findOrFail($Id); // Eager load category & parent
        return view('inventory.itemmaster.itemmasterlist.show', compact('item'));
    }

    public function edit($Id)
    {
        $item = ItemMasterList::with('category')->findOrFail($Id);
        $categories = ItemCategories::whereNull('ParentId')->get(); // Fetch only top-level categories
        return view('inventory.itemmaster.itemmasterlist.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $Id)
    {
        $item = ItemMasterList::findOrFail($Id);

        $validatedData = $request->validate([
            'ItemCode'      => 'required',
            'BarCode'       => 'required',
            'ItemName'      => 'required',
            'ItemType'      => 'required',
            'Category'      => 'required|exists:t_ItemCategory,Id',
            'SubCategory'   => 'nullable|exists:t_ItemCategory,Id', 
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

    // **Fetch Subcategories Dynamically**
    public function getSubcategories(Request $request)
    {
        $subcategories = ItemCategories::where('ParentId', $request->category_Id)->get(); // Fetch subcategories using ParentId
        return response()->json($subcategories);
    }
}
