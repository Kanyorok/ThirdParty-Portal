<?php

namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\StockItem;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon; 
use App\Models\Inventory\Store;
use App\Models\Core\Branch;

class SKUController extends Controller
{
    
    public function index()
    {
        $items = StockItem::all(); 
        return view('inventory.itemmaster.sku.index', compact('items'));
    }


    public function create()
{
    $categories = \App\Models\Inventory\ItemCategories::whereNull('ParentId')->get();
    $branches = Branch::all();
    $stores = [];
    return view('inventory.itemmaster.sku.create', compact('branches', 'stores', 'categories'));
}

public function getStores(Request $request)
{
    $Id = $request->get('BranchID');
    if (!$Id) {
        return response()->json([], 400);
    }
    $stores = Store::where('BranchID', $Id)->get(['Id', 'StoreName']);
    return response()->json($stores);
}



    public function store(Request $request)
{
    $validatedData = $request->validate([
        'Batch'         => 'required|boolean',
        'ItemID'      => 'required|exists:t_Items,Id',
        'Serial'        => 'required|boolean',
        'Perishable'    => 'required|boolean',
        'Saleable'      => 'required|boolean',
        'Purchasable'   => 'required|boolean',
        'Store' => 'required|integer|exists:t_Stores,Id',
        'Branch' => 'required|integer|exists:t_Branches,Id',
        'CurrentQty'    => 'required|integer|min:0',
        'Min'           => 'required|integer|min:0',
        'Reorder'       => 'required|integer|min:0',
        'Max'           => 'required|integer|min:0',
        'LastReceived'  => 'nullable|date',
        'Status'        => 'required|boolean',
    ]);
    
    try {
        $skuCode = DB::transaction(function () use ($validatedData) {
            // Lock the table and get the latest SKU in one operation
            $latestItem = DB::table('t_StockItems')
                ->lockForUpdate()
                ->orderBy('CreatedOn', 'desc')
                ->first();
                
            // Generate next SKU code based on the locked result
            $latestSku = $latestItem ? $latestItem->SKUCode : 'SKU-00000';
            $skuNumber = intval(substr($latestSku, 4)) + 1;
            $newSkuCode = 'SKU-' . str_pad($skuNumber, 5, '0', STR_PAD_LEFT);
            
            // Add SKUCode and timestamps to validated data
            $validatedData['SKUCode'] = $newSkuCode;
            $validatedData['CreatedBy'] = Auth::id();
            $validatedData['CreatedOn'] = Carbon::now();
            $validatedData['ModifiedBy'] = Auth::id();
            $validatedData['ModifiedOn'] = Carbon::now();
            
            // Create the item
            StockItem::create($validatedData);
            
            return $newSkuCode;
        });
        
        return redirect()->route('sku.index')->with('success', 'Stock item added successfully with SKU: ' . $skuCode);
    } catch (\Exception $e) {
        return back()->withErrors('Failed to create stock item: ' . $e->getMessage());
    }
}
    // Show details of a specific stock item
    public function show($Id)
    {
        $item = StockItem::findOrFail($Id);
        return view('inventory.itemmaster.sku.show', compact('item'));
    }



public function edit($Id)
{
    $item = StockItem::findOrFail($Id);
    $categories = \App\Models\Inventory\ItemCategories::whereNull('ParentId')->get();
    $branches = Branch::all();
    $stores = Store::where('BranchID', $item->Branch)->get();

    // Determine the relevant category or subcategory
    $categoryId = $item->item->category->parent ? $item->item->category->parent->Id : $item->item->category->Id;
    $subcategoryId = $item->item->category->parent ? $item->item->category->Id : null;

    // Fetch items for the selected (sub)category
    if ($subcategoryId) {
        $items = \App\Models\Inventory\ItemMasterList::where('Category', $subcategoryId)->get();
    } else {
        $items = \App\Models\Inventory\ItemMasterList::where('Category', $categoryId)->get();
    }

    return view('inventory.itemmaster.sku.edit', compact('item', 'branches', 'stores', 'categories', 'items'));
}

public function getItemsByCategoryOrSubcategory(Request $request)
{
    $categoryId = $request->get('category_id');
    $subcategoryId = $request->get('subcategory_id');

    if ($subcategoryId) {
        // Fetch items by subcategory
        $items = \App\Models\Inventory\ItemMasterList::where('Category', $subcategoryId)->get(['Id', 'ItemName']);
    } else {
        // Fetch items directly under the category (no subcategory selected)
        $items = \App\Models\Inventory\ItemMasterList::where('Category', $categoryId)->get(['Id', 'ItemName']);
    }
    return response()->json($items);
}


    // Update stock item details
  public function update(Request $request, $Id)
{
    $item = StockItem::findOrFail($Id);

    $validatedData = $request->validate([
        'ItemID'     => 'required|exists:t_Items,Id',
        'Batch'        => 'required|boolean',
        'Serial'       => 'required|boolean',
        'Perishable'   => 'required|boolean',
        'Saleable'     => 'required|boolean',
        'Purchasable'  => 'required|boolean',
        'Store'        => 'required|string|max:255',
        'Branch'       => 'required|string|max:255',
        'CurrentQty'   => 'required|integer|min:0',
        'Min'          => 'required|integer|min:0',
        'Reorder'      => 'required|integer|min:0',
        'Max'          => 'required|integer|min:0',
        'LastReceived' => 'nullable|date',
        'Status'       => 'required|boolean',
    ]);

    $validatedData['ModifiedBy'] = Auth::id();

    $item->update($validatedData);

    return redirect()->route('sku.index')->with('Success', 'Stock item updated successfully!');
}

    // Delete a stock item
    public function destroy($Id)
    {
        $item = StockItem::findOrFail($Id);
        $item->delete();

        return redirect()->route('sku.index')->with('success', '🗑️ Stock item deleted successfully!');
    }
}
