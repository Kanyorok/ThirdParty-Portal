<?php

namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\StockItem;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon; 

class SKUController extends Controller
{
    // Fetch all stock items
    public function index()
    {
        $items = StockItem::all(); 
        return view('inventory.itemmaster.sku.index', compact('items'));
    }

    // Show the form to create a new stock item
    public function create()
    {
        return view('inventory.itemmaster.sku.create');
    }

    // Store a new stock item



    public function store(Request $request)
{
    $validatedData = $request->validate([
        'Batch'         => 'required|boolean',
        'ItemType'      => 'required|string',
        'Serial'        => 'required|boolean',
        'Perishable'    => 'required|boolean',
        'Saleable'      => 'required|boolean',
        'Purchasable'   => 'required|boolean',
        'Store'         => 'required|string|max:255',
        'Branch'        => 'required|string|max:255',
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

    // Show the edit form for a stock item
    public function edit($Id)
    {
        $item = StockItem::findOrFail($Id);
        return view('inventory.itemmaster.sku.edit', compact('item'));
    }

    // Update stock item details
  public function update(Request $request, $Id)
{
    $item = StockItem::findOrFail($Id);

    $validatedData = $request->validate([
        'ItemType'     => 'required|string',
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

    return redirect()->route('sku.index')->with('success', 'Stock item updated successfully!');
}

    // Delete a stock item
    public function destroy($Id)
    {
        $item = StockItem::findOrFail($Id);
        $item->delete();

        return redirect()->route('sku.index')->with('success', '🗑️ Stock item deleted successfully!');
    }
}
