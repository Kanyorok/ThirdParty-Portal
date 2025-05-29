<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockItemRequest;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\Store;
use App\Models\Inventory\ItemMasterList;
use App\Models\Core\Branch;
use App\Services\Inventory\StockItemService;
use Illuminate\Http\Request;

class SKUController extends Controller
{
    protected StockItemService $stockItemService;

    public function __construct(StockItemService $stockItemService)
    {
        $this->stockItemService = $stockItemService;
    }

    public function index()
    {
        $this->authorize('viewAny', StockItem::class);

        $items = StockItem::all();
        return view('inventory.itemmaster.sku.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('create', StockItem::class);

        $categories = ItemCategories::whereNull('ParentId')->get();
        $branches = Branch::all();
        $stores = [];

        return view('inventory.itemmaster.sku.create', compact('branches', 'stores', 'categories'));
    }

    public function store(StockItemRequest $request)
    {
        $this->authorize('create', StockItem::class);

        $data = $request->validated();

        try {
            $skuCode = $this->stockItemService->create($data);
            return redirect()->route('sku.index')->with('success', 'Stock item added successfully with SKU: ' . $skuCode);
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create stock item: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $item = StockItem::findOrFail($id);
        $this->authorize('view', $item);

        return view('inventory.itemmaster.sku.show', compact('item'));
    }

    public function edit($id)
    {
        $item = StockItem::findOrFail($id);
        $this->authorize('update', $item);

        $categories = ItemCategories::whereNull('ParentId')->get();
        $branches = Branch::all();
        $stores = Store::where('BranchID', $item->Branch)->get();

        $categoryId = $item->item->category->parent ? $item->item->category->parent->Id : $item->item->category->Id;
        $subcategoryId = $item->item->category->parent ? $item->item->category->Id : null;

        $items = ItemMasterList::where('Category', $subcategoryId ?? $categoryId)->get();

        return view('inventory.itemmaster.sku.edit', compact('item', 'branches', 'stores', 'categories', 'items'));
    }

    public function update(StockItemRequest $request, $id)
    {
        $item = StockItem::findOrFail($id);
        $this->authorize('update', $item);

        $data = $request->validated();

        try {
            $this->stockItemService->update($item, $data);
            return redirect()->route('sku.index')->with('success', 'Stock item updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update stock item: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $item = StockItem::findOrFail($id);
        $this->authorize('destroy', $item); // Laravel convention uses 'delete'

        try {
            $this->stockItemService->delete($item);
            return redirect()->route('sku.index')->with('success', '🗑️ Stock item deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete stock item: ' . $e->getMessage());
        }
    }

    // AJAX: get stores by branch
    public function getStores(Request $request)
    {
        $branchId = $request->get('BranchID');
        if (!$branchId) {
            return response()->json([], 400);
        }

        $stores = Store::where('BranchID', $branchId)->get(['Id', 'StoreName']);
        return response()->json($stores);
    }

    // AJAX: get items by category or subcategory
    public function getItemsByCategoryOrSubcategory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $subcategoryId = $request->get('subcategory_id');

        $items = ItemMasterList::where('Category', $subcategoryId ?? $categoryId)->get(['Id', 'ItemName']);
        return response()->json($items);
    }
}
