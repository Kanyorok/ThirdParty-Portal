<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\UnitOfMeasure;

class ItemMasterListController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Datatables::of(ItemMasterList::with('category.parent'))
                ->addIndexColumn()
                ->addColumn('Category', fn($item) => optional($item->category)->Name ?? 'Uncategorized')
                ->addColumn('ParentCategory', fn($item) => optional(optional($item->category)->parent)->Name ?? '—')

                ->addColumn('ItemType', fn($item) => optional($item->itemType)->TypeName ?? '—')
                ->addColumn('InventoryType', fn($item) => optional($item->inventoryType)->Type ?? '—')
                ->addColumn('UOM', fn($item) => optional($item->uom)->Code ?? '—')
                ->addColumn('Action', function ($item) {
                    return '
                        <a href="' . route('itemmasterlist.show', $item->Id) . '" class="btn btn-sm btn-primary">View</a>
                        <a href="' . route('itemmasterlist.edit', $item->Id) . '" class="btn btn-sm btn-warning">Edit</a>
                        <button onclick="confirmDelete('.$item->Id.')" class="btn btn-danger btn-sm">Delete</button>
                        <form id="delete-form-'.$item->Id.'" action="'.route('itemmasterlist.destroy', $item->Id).'" method="POST" style="display:none;">
                        '.csrf_field().'
                       '.method_field('DELETE').'
                        </form>
                       ';
    })
    ->rawColumns(['Action'])
    ->make(true);
                    
        }

        return view('inventory.itemmaster.itemmasterlist.index');
    }


    public function create()
    {
        $this->authorize('create', ItemMasterList::class);
        return view('inventory.itemmaster.itemmasterlist.create', [
            
            'categories' => ItemCategories::whereNull('ParentId')->get(),
            'itemTypes' => ItemType::all(),
            'uoms' => UnitOfMeasure::all(),
            'inventoryTypes' => InventoryType::all(),
        ]);
    }

  
    public function store(Request $request)
    {
        $this->authorize('create', ItemMasterList::class);
        $validatedData = $request->validate([
            'BarCode' => 'required|string|max:255',
            'ItemName' => 'required|string|max:255',
            'ItemType' => 'required|exists:t_ItemTypes,Id',
            'Category' => 'required|exists:t_ItemCategories,Id',
            'UOM' => 'required|exists:t_UOM,Id',
            'InventoryType' => 'required|exists:t_InventoryTypes,Id',
            'ImageUpload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'DocumentUpload' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
            'ItemDescription' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validatedData, $request) {
            $item = new ItemMasterList();
            $item->fill($validatedData);
            $item->CreatedBy = Auth::id();
            $item->CreatedOn = Carbon::now();
            $item->ModifiedBy = Auth::id();
            $item->ModifiedOn = Carbon::now();

            // Handle image upload
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

            // Handle document upload
            if ($request->hasFile('DocumentUpload')) {
                $item->DocumentUpload = $request->file('DocumentUpload')->store('items/documents', 'public');
            }

            // Set category or subcategory
            $item->Category = $request->SubCategory ?: $request->Category;
            $item->save();

            // Generate ItemCode using the Id and save again
            $item->ItemCode = 'ITM-' . str_pad($item->Id, 5, '0', STR_PAD_LEFT);
            $item->save();
            
        });

        return redirect()->route('itemmaster.index')->with('success', 'Item created successfully.');
    }

    public function show($Id)
    {
        $item = ItemMasterList::with('category.parent')->findOrFail($Id);
        $this->authorize('view', $item);
        return view('inventory.itemmaster.itemmasterlist.show', compact('item'));
    }

   
    public function edit($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $this->authorize('update', $item);
        return view('inventory.itemmaster.itemmasterlist.edit', [
            'item' => $item,
            'categories' => ItemCategories::whereNull('ParentId')->get(),
            'subcategories' => ItemCategories::where('ParentId', $item->category?->ParentId ?? $item->Category)->get(),
            'itemTypes' => ItemType::all(),
            'uoms' => UnitOfMeasure::all(),
            'inventoryTypes' => InventoryType::all(),
        ]);
    }


public function update(Request $request, $Id)
{
    $item = ItemMasterList::findOrFail($Id);
    $this->authorize('update', $item);

    $validatedData = $request->validate([
        'BarCode' => 'required|string|max:255',
        'ItemName' => 'required|string|max:255',
        'ItemType' => 'required|exists:t_ItemTypes,Id',
        'Category' => 'required|exists:t_ItemCategories,Id',
        'UOM' => 'required|exists:t_UOM,Id',
        'InventoryType' => 'required|exists:t_InventoryTypes,Id',
        'ImageUpload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'DocumentUpload' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls|max:5120',
        'ItemDescription' => 'nullable|string',
    ]);

    $item->fill($validatedData);
    $item->ModifiedBy = Auth::id();
    $item->ModifiedOn = Carbon::now();

   
    $item->Category = $request->SubCategory ?: $request->Category;

    // Handle image removal
    if ($request->input('remove_image') == '1' && $item->ImageId) {
        \App\Models\DMS\Image::destroy($item->ImageId);
        $item->ImageId = null;
    }

    // Handle new image upload
    if ($request->hasFile('ImageUpload')) {
        if ($item->ImageId) {
            \App\Models\DMS\Image::destroy($item->ImageId);
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

    // Handle document upload
    if ($request->hasFile('DocumentUpload')) {
        $item->DocumentUpload = $request->file('DocumentUpload')->store('items/documents', 'public');
    }

    $item->save();

    return redirect()->route('itemmaster.index')->with('success', 'Item updated successfully.');
}

    // Delete item
    public function destroy($Id)
    {
        $item = ItemMasterList::findOrFail($Id);
        $this->authorize('destroy', $item);
        $item->DeletedBy = Auth::id();
        $item->DeletedOn = Carbon::now();
        $item->save();
        $item->delete();

        return redirect()->route('itemmaster.index')->with('success', 'Item Deleted successfully.');
    }

    // Get subcategories dynamically
    public function getSubcategories(Request $request)
    {
        return response()->json(ItemCategories::where('ParentId', $request->get('category_id'))->get(['Id', 'Name']));
    }
}
