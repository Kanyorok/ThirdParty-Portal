<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemMasterListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Datatables::of(ItemMasterList::with('category.parent'))
                ->addIndexColumn()
                ->addColumn('Category', function ($item) {
                    return optional($item->category)->Name ?? '—';
                })
                ->addColumn('ParentCategory', function ($item) {
                    return optional($item->category->parent)->Name ?? '—';
                })
                ->addColumn('Action', function ($item) {
                    return '
                        <a href="' . route('itemmasterlist.show', ['Id' => $item->Id]) . '" class="btn btn-sm btn-primary">View</a>
                        <a href="' . route('itemmasterlist.edit', ['Id' => $item->Id]) . '" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" onclick="confirmDelete(' . $item->getKey() . ')" class="btn btn-sm btn-danger">Delete</a>';
                })
                ->rawColumns(['Action'])
                ->make(true);
        }

        return view('inventory.itemmaster.itemmasterlist.index');
    }

    public function create()
    {
        $categories = ItemCategories::whereNull('ParentId')->get(); // Main categories
        return view('inventory.itemmaster.itemmasterlist.create', compact('categories'));
    }

public function store(Request $request)
{
    $request->validate([
        'BarCode'         => 'required|string|max:255',
        'ItemName'        => 'required|string|max:255',
        'ItemType'        => 'required|string|max:255',
        'Category'        => 'required|exists:t_ItemCategories,Id',
        'UOM'             => 'required|string|max:255',
        'InventoryType'   => 'required|string|max:255',
        'ImageUpload'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'ItemDescription' => 'nullable|string',
        'DocumentUpload'  => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
    ]);

    DB::transaction(function () use ($request) {
        $item = new ItemMasterList();
        $item->fill($request->except('ImageUpload', 'DocumentUpload'));

        $item->CreatedBy = Auth::id();
        $item->CreatedOn = Carbon::now();
        $item->ModifiedBy = Auth::id();
        $item->ModifiedOn = Carbon::now();


if ($request->hasFile('ImageUpload')) {
    $file = $request->file('ImageUpload');
    $imageContent = base64_encode(file_get_contents($file->getRealPath()));
    $image = \App\Models\DMS\Image::create([
        'Name' => $file->getClientOriginalName(),
        'Image' => $imageContent,
        'MIMEType' => $file->getMimeType(),
        'CreatedBy' => Auth::id(),
        'CreatedOn' => now(),
        'ModifiedBy' => Auth::id(),
        'ModifiedOn' => now(),
    ]);
    $item->ImageId = $image->ImageID;
}

        if ($request->hasFile('DocumentUpload')) {
            $docPath = $request->file('DocumentUpload')->store('items/documents', 'public');
            $item->DocumentUpload = $docPath;
        }

        // Set category or subcategory
        $item->Category = $request->SubCategory ?: $request->Category;

        // Save first to get the auto-incremented Id
        $item->save();

        // Generate ItemCode using the Id and save again
        $item->ItemCode = 'ITM-' . str_pad($item->Id, 5, '0', STR_PAD_LEFT);
        $item->save();
    });

    return redirect()->route('itemmaster.index')->with('success', 'Item created successfully.');
}
    public function edit($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $categories = ItemCategories::whereNull('ParentId')->get(); // For parent dropdown
        $subcategories = ItemCategories::where('ParentId', $item->category?->ParentId ?? $item->Category)->get();
        return view('inventory.itemmaster.itemmasterlist.edit', compact('item', 'categories', 'subcategories'));
    }

    public function update(Request $request, $Id)
    {
        $request->validate([
            'BarCode'         => 'required|string|max:255',
            'ItemName'        => 'required|string|max:255',
            'ItemType'        => 'required|string|max:255',
            'Category'        => 'required|exists:t_ItemCategories,Id',
            'UOM'             => 'required|string|max:255',
            'InventoryType'   => 'required|string|max:255',
            'ImageUpload'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'DocumentUpload'  => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
            'ItemDescription' => 'nullable|string',
        ]);

        $item = ItemMasterList::findOrFail($Id);
        $item->fill($request->except('ImageUpload'));

        $item->Category = $request->Category;
        $item->ModifiedBy = Auth::id();
        $item->ModifiedOn = Carbon::now();

        if ($request->hasFile('ImageUpload')) {
            // Delete old image if exists
            if ($item->ImageId) {
                $oldImage = \App\Models\DMS\Image::find($item->ImageId);
                if ($oldImage) {
                    $oldImage->delete();
                }
            }

            if ($request->input('remove_image') == '1' && $item->ImageId) {
    $oldImage = \App\Models\DMS\Image::find($item->ImageId);
    if ($oldImage) {
        $oldImage->delete();
    }
    $item->ImageId = null;
}

            $file = $request->file('ImageUpload');
            $imageContent = base64_encode(file_get_contents($file->getRealPath()));
            $image = \App\Models\DMS\Image::create([
                'Name' => $file->getClientOriginalName(),
                'Image' => $imageContent,
                'MIMEType' => $file->getMimeType(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);
            $item->ImageId = $image->ImageID;
        }
        if ($request->hasFile('DocumentUpload')) {
            $docPath = $request->file('DocumentUpload')->store('items/documents', 'public');
            $item->DocumentUpload = $docPath;
}

        $item->save();

        return redirect()->route('itemmaster.index')->with('success', 'Item updated successfully.');
    }

    public function show($Id)
    {
        $item = ItemMasterList::with('category.parent')->findOrFail($Id);
        return view('inventory.itemmaster.itemmasterlist.show', compact('item'));
    }

    public function destroy($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $item->DeletedBy = Auth::id();
        $item->DeletedOn = Carbon::now();
        $item->save();
        $item->delete();

        return response()->json(['success' => 'Item deleted successfully.']);
    }

    public function getSubcategories(Request $request)
    {
        $categoryId = $request->get('category_id');

        if (!$categoryId) {
            return response()->json([], 400);
        }

        $subcategories = ItemCategories::where('ParentId', $categoryId)->get(['Id', 'Name']);

        return response()->json($subcategories);
    }
}
